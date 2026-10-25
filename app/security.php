<?php
declare(strict_types=1);
function session_boot(): void {
    global $config;
    if (session_status()===PHP_SESSION_ACTIVE) return;
    $sessionDirectory = $config['session_directory'] ?? ROOT . '/storage/sessions';
    if (!is_dir($sessionDirectory) && !mkdir($sessionDirectory, 0700, true) && !is_dir($sessionDirectory)) {
        throw new RuntimeException('Session storage is unavailable.');
    }
    session_save_path($sessionDirectory);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name($config['session_name']??'graphite_admin');
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$config['secure_cookies']??true,'httponly'=>true,'samesite'=>'Lax']);
    if (!session_start()) throw new RuntimeException('Unable to start session.');
    if (isset($_SESSION['last_seen']) && time()-$_SESSION['last_seen']>3600) {
        $_SESSION=[];
        session_regenerate_id(true);
    }
    $_SESSION['last_seen']=time();
    if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
}
function current_user(): ?array {
    session_boot();
    if(empty($_SESSION['uid']))return null;
    $u=query('SELECT id,name,email,role,active,session_version FROM users WHERE id=?',[$_SESSION['uid']])->fetch();
    if(!$u||!(int)$u['active']||(int)($u['session_version']??1)!==($_SESSION['version']??0)){
        unset($_SESSION['uid']);
        return null;
    }
    unset($u['session_version']);
    return $u;
}
function require_user(bool $admin=false): array {
    $u=current_user();
    if(!$u)fail('Please sign in.',401);
    if($admin&&$u['role']!=='admin')fail('Administrator access is required.',403);
    return $u;
}
function rate_limit(string $bucket,int $max,int $window): void {
    global $config;
    $key=$config['rate_limit_key']??'';
    if(strlen($key)<32)throw new RuntimeException('Set a strong rate_limit_key.');
    $identity=hash_hmac('sha256',$bucket.'|'.($_SERVER['REMOTE_ADDR']??'unknown'),$key);
    // Storage contains short-lived keyed hashes, never raw IP addresses.
    db()->beginTransaction();
    try{
        query('DELETE FROM rate_limits WHERE expires_at < ?',[time()]);
        $driver=db()->getAttribute(PDO::ATTR_DRIVER_NAME);
        $sql=$driver==='mysql'?'INSERT INTO rate_limits (identity,hits,expires_at) VALUES (?,1,?) ON DUPLICATE KEY UPDATE hits=hits+1':'INSERT INTO rate_limits (identity,hits,expires_at) VALUES (?,1,?) ON CONFLICT(identity) DO UPDATE SET hits=hits+1';
        query($sql,[$identity,time()+$window]);
        $r=query('SELECT hits FROM rate_limits WHERE identity=?',[$identity])->fetch();
        db()->commit();
    }
    catch(Throwable $e){
        db()->rollBack();
        throw $e;
    }
    if((int)$r['hits']>$max)fail('Too many requests. Please try again later.',429);
}
function security_headers():void {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'; frame-ancestors 'self'; base-uri 'self'; form-action 'self'");
}
