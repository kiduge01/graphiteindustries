<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') {
    http_response_code(404);
    exit;
}
require __DIR__.'/../app/bootstrap.php';
$schema=file_get_contents(__DIR__.'/../database/schema.sql');
if(db()->getAttribute(PDO::ATTR_DRIVER_NAME)==='sqlite')$schema=str_replace('INTEGER PRIMARY KEY AUTO_INCREMENT','INTEGER PRIMARY KEY AUTOINCREMENT',$schema);
if(query("SELECT COUNT(*) FROM ".(db()->getAttribute(PDO::ATTR_DRIVER_NAME)==='mysql'?'information_schema.tables WHERE table_schema=DATABASE() AND table_name=\'users\'':"sqlite_master WHERE type='table' AND name='users'"))->fetchColumn()>0){
    fwrite(STDERR,"Database is already initialised. No changes made.\n");
    exit(1);
}
db()->exec($schema);
$seed=json_decode(file_get_contents(__DIR__.'/../app/seed.json'),true,512,JSON_THROW_ON_ERROR);
$now=gmdate('Y-m-d H:i:s');
db()->beginTransaction();
foreach($seed['pages'] as $p)query('INSERT INTO pages (id,title,eyebrow,summary,body,image,seo,status,updated_at) VALUES (?,?,?,?,?,?,?,?,?)',[$p['id'],$p['title'],$p['eyebrow'],$p['summary'],$p['body'],$p['image'],$p['seo'],$p['status'],$now]);
foreach($seed['settings'] as $k=>$v)query('INSERT INTO settings (setting_key,setting_value) VALUES (?,?)',[$k,$v]);
foreach($seed['media'] as $m)query('INSERT INTO media (name,url,type,created_at) VALUES (?,?,?,?)',[$m['name'],$m['url'],$m['type'],$now]);
db()->commit();
echo "Database initialised. Create your administrator with php bin/user.php.\n";
