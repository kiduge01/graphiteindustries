<?php
declare(strict_types=1);
function fail(string $message,int $code=400): never {
    http_response_code($code);
    echo json_encode(['error'=>$message]);
    exit;
}
function output(array $data): never {
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
}
function text(array $data,string $key,int $max=1000,bool $required=false): string {
    $v=$data[$key]??'';
    if(!is_string($v))fail('Invalid '.$key.'.');
    $v=trim($v);
    if(strlen($v)>$max||($required&&$v===''))fail('Please check '.$key.'.');
    return $v;
}
function valid_image(string $s): string {
    if($s!==''&&!preg_match('~^(assets/|uploads/)[a-zA-Z0-9._/-]+$~',$s))fail('Select an uploaded image.');
    if(str_contains($s,'..'))fail('Invalid file path.');
    return $s;
}
