<?php
declare(strict_types=1);

// Staff notifications. Delivery is optional and never blocks storing an enquiry:
// the CMS inbox stays the record, and any failure is written to the PHP error log.
// Configure in app/config.php:
//   'mail' => ['enabled' => true, 'transport' => 'smtp', 'host' => 'mail.example.com',
//              'port' => 587, 'encryption' => 'tls', 'username' => '...', 'password' => '...',
//              'from' => 'website@example.com', 'from_name' => 'Company website',
//              'to' => 'team@example.com']
// 'transport' => 'mail' uses PHP mail() instead. 'to' defaults to the CMS
// "notify_email" setting, then the public company email.

function mail_config(): array
{
    global $config;
    $mail = $config['mail'] ?? [];
    return is_array($mail) ? $mail : [];
}

function mail_enabled(): bool
{
    $mail = mail_config();
    return !empty($mail['enabled']) && filter_var($mail['from'] ?? '', FILTER_VALIDATE_EMAIL);
}

function mail_header_text(string $value): string
{
    return trim(preg_replace('/[\r\n\t]+/', ' ', $value));
}

function mail_encode_header(string $value): string
{
    $value = mail_header_text($value);
    return preg_match('/[^\x20-\x7E]/', $value) ? '=?UTF-8?B?' . base64_encode($value) . '?=' : $value;
}

function send_mail(string $to, string $subject, string $body, ?string $replyTo = null): bool
{
    if (!mail_enabled() || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $mail = mail_config();
    $from = $mail['from'];
    $fromName = mail_encode_header($mail['from_name'] ?? 'Website');
    $headers = [
        'Date: ' . date(DATE_RFC2822),
        'From: ' . $fromName . ' <' . $from . '>',
        'To: <' . $to . '>',
        'Subject: ' . mail_encode_header($subject),
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@' . (substr(strrchr($from, '@'), 1) ?: 'localhost') . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
        'Auto-Submitted: auto-generated',
    ];
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
        $headers[] = 'Reply-To: <' . $replyTo . '>';
    }
    $encoded = rtrim(chunk_split(base64_encode(str_replace(["\r\n", "\r"], "\n", $body)), 76, "\r\n"));
    try {
        if (($mail['transport'] ?? 'smtp') === 'mail') {
            $extra = array_filter($headers, fn($h) => !preg_match('/^(To|Subject):/', $h));
            return mail($to, mail_encode_header($subject), $encoded, implode("\r\n", $extra), '-f' . $from);
        }
        smtp_send($mail, $from, $to, implode("\r\n", $headers) . "\r\n\r\n" . $encoded);
        return true;
    } catch (Throwable $e) {
        error_log('Graphite mail: ' . $e->getMessage());
        return false;
    }
}

function smtp_send(array $mail, string $from, string $to, string $message): void
{
    $host = (string)($mail['host'] ?? '');
    if ($host === '') {
        throw new RuntimeException('SMTP host is not configured.');
    }
    $encryption = $mail['encryption'] ?? 'tls';
    $port = (int)($mail['port'] ?? ($encryption === 'ssl' ? 465 : 587));
    $context = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'peer_name' => $host]]);
    $socket = stream_socket_client(($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port, $errno, $error, 15, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        throw new RuntimeException("SMTP connection failed: $error");
    }
    stream_set_timeout($socket, 15);
    $read = function (array $expect) use ($socket): string {
        $response = '';
        while (($line = fgets($socket, 1000)) !== false) {
            $response .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') break;
        }
        if (!in_array((int)substr($response, 0, 3), $expect, true)) {
            throw new RuntimeException('SMTP error: ' . trim($response));
        }
        return $response;
    };
    $send = function (string $command, array $expect) use ($socket, $read): string {
        fwrite($socket, $command . "\r\n");
        return $read($expect);
    };
    try {
        $read([220]);
        $hostname = preg_replace('/[^a-z0-9.-]/i', '', gethostname() ?: 'localhost') ?: 'localhost';
        $features = $send('EHLO ' . $hostname, [250]);
        if ($encryption === 'tls') {
            $send('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                throw new RuntimeException('SMTP STARTTLS negotiation failed.');
            }
            $features = $send('EHLO ' . $hostname, [250]);
        }
        if (($mail['username'] ?? '') !== '') {
            if ($encryption === 'none') {
                throw new RuntimeException('Refusing to send SMTP credentials without encryption.');
            }
            $send('AUTH LOGIN', [334]);
            $send(base64_encode((string)$mail['username']), [334]);
            $send(base64_encode((string)($mail['password'] ?? '')), [235]);
        }
        $send('MAIL FROM:<' . $from . '>', [250]);
        $send('RCPT TO:<' . $to . '>', [250, 251]);
        $send('DATA', [354]);
        // Dot-stuffing keeps a line containing only "." from ending the message early.
        $send(preg_replace('/^\./m', '..', $message) . "\r\n.", [250]);
        $send('QUIT', [221]);
    } finally {
        fclose($socket);
    }
}

function notify_enquiry(array $settings, array $enquiry): void
{
    $to = mail_config()['to'] ?? '';
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) $to = $settings['notify_email'] ?? '';
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) $to = $settings['email'] ?? '';
    $body = "A new website enquiry has been received.\n\n"
        . 'Name: ' . $enquiry['name'] . "\n"
        . 'Email: ' . $enquiry['email'] . "\n"
        . 'Organisation: ' . ($enquiry['organisation'] !== '' ? $enquiry['organisation'] : '-') . "\n"
        . 'Category: ' . $enquiry['category'] . "\n\n"
        . $enquiry['message'] . "\n\n"
        . "Reply to this email to respond directly. The enquiry is also stored in the website CMS inbox.\n";
    send_mail($to, 'Website enquiry: ' . $enquiry['category'] . ' from ' . $enquiry['name'], $body, $enquiry['email']);
}
