<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){
    http_response_code(404);
    exit;
}
require __DIR__.'/../app/bootstrap.php';
function prompt(string $label):string {
    echo $label;
    return trim(fgets(STDIN));
}
$name=prompt('Name: ');
$email=prompt('Email: ');
if(!filter_var($email,FILTER_VALIDATE_EMAIL)||$name===''){
    fwrite(STDERR,"Invalid name or email.\n");
    exit(1);
}
$hide=function_exists('shell_exec')&&DIRECTORY_SEPARATOR==='/';
if($hide)shell_exec('stty -echo');
try{
    $password=prompt('Password (minimum 12 characters): ');
}
finally{
    if($hide){
        shell_exec('stty echo');
        echo "\n";
    }
}
if(strlen($password)<12){
    fwrite(STDERR,"Password is too short.\n");
    exit(1);
}
$exists=query('SELECT id FROM users WHERE email=?',[$email])->fetch();
if($exists){
    query('UPDATE users SET password_hash=?,session_version=session_version+1 WHERE id=?',[password_hash($password,PASSWORD_DEFAULT),$exists['id']]);
    echo "Password reset; existing sessions revoked.\n";
}
else{
    query('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,?)',[$name,$email,password_hash($password,PASSWORD_DEFAULT),'admin',gmdate('Y-m-d H:i:s')]);
    echo "Administrator created.\n";
}
