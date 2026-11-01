<?php
declare(strict_types=1);
// Humans need a few seconds to complete a form; scripted submissions are faster.
function require_public_form(): void
{
    global $config;
    session_boot();
    if (!hash_equals($_SESSION['csrf'], $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) fail('Refresh this page and try again.', 403);
    if (time() - (int)($_SESSION['form_issued'] ?? time()) < (int)($config['form_minimum_seconds'] ?? 3)) fail('Please wait a moment and try again.', 429);
}
if($action==='health'){
    session_boot();
    $_SESSION['form_issued'] ??= time();
    output(['ok'=>true,'csrf'=>$_SESSION['csrf']]);
}
if ($action === 'public') {
    require_once ROOT . '/app/public-content.php';
    $settings = array_column(all('SELECT * FROM settings'), 'setting_value', 'setting_key');
    $postId = isset($_GET['post']) ? filter_var($_GET['post'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null;
    if ($postId === false) {
        fail('Invalid post.', 404);
    }
    $news = published_news(page_number('newsPage'), $postId);
    if ($news['page'] > $news['pages'] || ($postId && !$news['post'])) {
        fail('News not found.', 404);
    }
    unset($settings['notify_email']);
    output([
        'pages' => all("SELECT * FROM pages WHERE status='published'"),
        'posts' => $news['posts'],
        'selectedPost' => $news['post'],
        'newsPagination' => ['page' => $news['page'], 'pages' => $news['pages'], 'total' => $news['total']],
        'legal' => legal_pages($settings['email'] ?? ''),
        'settings' => $settings,
    ]);
}
if($action==='enquire'){
    require_public_form();
    rate_limit('enquiry',5,3600);
    if(text($data,'website',500)!=='')fail('Unable to submit enquiry.');
    $name=text($data,'name',120,true);
    $email=text($data,'email',190,true);
    if(!filter_var($email,FILTER_VALIDATE_EMAIL))fail('Enter a valid email.');
    $message=text($data,'message',5000,true);
    if(strlen($message)<10)fail('Please add more detail to your message.');
    if(preg_match_all('~https?://~i',$message)>3)fail('Please remove some links from your message.');
    $enquiry=['name'=>$name,'email'=>$email,'organisation'=>text($data,'organisation',190),'category'=>text($data,'category',120,true),'message'=>$message];
    query('INSERT INTO enquiries (name,email,organisation,category,message,status,created_at) VALUES (?,?,?,?,?,?,?)',[$name,$email,$enquiry['organisation'],$enquiry['category'],$message,'new',gmdate('Y-m-d H:i:s')]);
    require_once ROOT . '/app/mailer.php';
    notify_enquiry(array_column(all('SELECT * FROM settings'), 'setting_value', 'setting_key'), $enquiry);
    output(['ok'=>true]);
}
// Update subscriptions are stored in the enquiry inbox (category "Project updates")
// so staff can filter and export them with the existing CSV tools.
if($action==='subscribe'){
    require_public_form();
    rate_limit('subscribe',5,3600);
    if(text($data,'website',500)!=='')fail('Unable to subscribe.');
    $email=text($data,'email',190,true);
    if(!filter_var($email,FILTER_VALIDATE_EMAIL))fail('Enter a valid email.');
    if(($data['consent']??'')!=='on')fail('Please accept the privacy notice to subscribe.');
    $exists=query("SELECT id FROM enquiries WHERE email=? AND category='Project updates'",[$email])->fetch();
    if(!$exists){
        query('INSERT INTO enquiries (name,email,organisation,category,message,status,created_at) VALUES (?,?,?,?,?,?,?)',['Updates subscriber',$email,'','Project updates','Requested verified company updates by email through the website footer form. Consent recorded with this entry.','new',gmdate('Y-m-d H:i:s')]);
    }
    output(['ok'=>true]);
}
if($action==='track'){
    session_boot();
    if(!hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_CSRF_TOKEN']??''))fail('Invalid session.',403);
    rate_limit('analytics',200,3600);
    $kind=text($data,'kind',20,true);
    if(!in_array($kind,['page','download'],true))fail('Invalid event.');
    $sid=text($data,'sid',64,true);
    if(!preg_match('/^[a-zA-Z0-9-]{16,64}$/',$sid))fail('Invalid session ID.');
    $source=parse_url(text($data,'referrer',2000),PHP_URL_HOST)?:'Direct';
    query('INSERT INTO events (kind,page,sid,source,created_at) VALUES (?,?,?,?,?)',[$kind,text($data,'page',100,true),$sid,substr($source,0,190),gmdate('Y-m-d H:i:s')]);
    output(['ok'=>true]);
}
