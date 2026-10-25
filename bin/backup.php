<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){
    http_response_code(404);
    exit;
}
require __DIR__.'/../app/bootstrap.php';
$dir=$config['backup_directory']??ROOT.'/storage/backups';
if(!is_dir($dir))mkdir($dir,0700,true);
$tables=['users','pages','posts','settings','media','enquiries','revisions','events','rate_limits'];
$data=['created_at'=>gmdate('c'),'schema_version'=>2,'tables'=>[]];
// Repeatable transaction protects against partial database snapshots.
db()->beginTransaction();
foreach($tables as $table)$data['tables'][$table]=all('SELECT * FROM '.$table);
db()->commit();
$path=$dir.'/graphite-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(3)).'.zip';
$zip=new ZipArchive();
if($zip->open($path,ZipArchive::CREATE|ZipArchive::EXCL)!==true)throw new RuntimeException('Cannot create backup.');
$zip->addFromString('database.json',json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
$upload=$config['uploads_directory']??ROOT.'/public/uploads';
$files=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($upload,FilesystemIterator::SKIP_DOTS));
foreach($files as $file){
    if($file->isFile()&&!$file->isLink())$zip->addFile($file->getPathname(),'uploads/'.substr($file->getPathname(),strlen($upload)+1));
}
$zip->close();
chmod($path,0600);
$backups=glob($dir.'/graphite-*.zip');
rsort($backups);
foreach(array_slice($backups,7) as $old)unlink($old);
echo 'Backup created outside the public document root. '.count($tables).' tables included; newest 7 archives retained.'.PHP_EOL;
