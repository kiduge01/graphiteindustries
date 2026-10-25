<?php
declare(strict_types=1);
if($action==='me'){
    $u=current_user();
    output(['user'=>$u,'csrf'=>$_SESSION['csrf']]);
}
if($action==='login'){
    rate_limit('login',10,900);
    $email=text($data,'email',190,true);
    $password=text($data,'password',1000,true);
    $u=query('SELECT * FROM users WHERE email=?',[$email])->fetch();
    if(!$u||!(int)$u['active']||!password_verify($password,$u['password_hash']))fail('The email or password is incorrect.',401);
    session_regenerate_id(true);
    $_SESSION['uid']=(int)$u['id'];
    $_SESSION['version']=(int)$u['session_version'];
    $_SESSION['csrf']=bin2hex(random_bytes(32));
    output(['user'=>current_user(),'csrf'=>$_SESSION['csrf']]);
}
if($action==='logout'){
    $_SESSION=[];
    session_destroy();
    output(['ok'=>true]);
}
if($action==='password'){
    $current=text($data,'current',1000,true);
    $password=text($data,'password',1000,true);
    if(strlen($password)<12)fail('Use at least 12 characters.');
    $hash=query('SELECT password_hash FROM users WHERE id=?',[$user['id']])->fetchColumn();
    if(!password_verify($current,$hash))fail('Current password is incorrect.');
    query('UPDATE users SET password_hash=?,session_version=session_version+1 WHERE id=?',[password_hash($password,PASSWORD_DEFAULT),$user['id']]);
    $_SESSION['version']++;
    session_regenerate_id(true);
    output(['ok'=>true]);
}
