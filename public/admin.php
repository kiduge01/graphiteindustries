<?php
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
try{
    require __DIR__.'/../app/bootstrap.php';
    security_headers();
}
catch(Throwable $e){
}
readfile(__DIR__.'/admin.html');
