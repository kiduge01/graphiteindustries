<?php
declare(strict_types=1);
if($action==='savePage'){
    $id=text($data,'id',40,true);
    $old=query('SELECT * FROM pages WHERE id=?',[$id])->fetch();
    if(!$old)fail('Page not found.',404);
    $status=text($data,'status',20,true);
    if(!in_array($status,['draft','published'],true))fail('Invalid status.');
    db()->beginTransaction();
    query('INSERT INTO revisions (page_id,content,created_at) VALUES (?,?,?)',[$id,json_encode($old),gmdate('Y-m-d H:i:s')]);
    query('UPDATE pages SET title=?,eyebrow=?,summary=?,body=?,image=?,seo=?,status=?,updated_at=? WHERE id=?',[text($data,'title',200,true),text($data,'eyebrow',160),text($data,'summary',1000),text($data,'body',30000),valid_image(text($data,'image',500)),text($data,'seo',200),$status,gmdate('Y-m-d H:i:s'),$id]);
    db()->commit();
    output(['ok'=>true]);
}
if($action==='restore'){
    $r=query('SELECT * FROM revisions WHERE id=?',[(int)($data['id']??0)])->fetch();
    if(!$r)fail('Revision not found.',404);
    $p=json_decode($r['content'],true);
    $old=query('SELECT * FROM pages WHERE id=?',[$r['page_id']])->fetch();
    db()->beginTransaction();
    query('INSERT INTO revisions (page_id,content,created_at) VALUES (?,?,?)',[$r['page_id'],json_encode($old),gmdate('Y-m-d H:i:s')]);
    query('UPDATE pages SET title=?,eyebrow=?,summary=?,body=?,image=?,seo=?,status=?,updated_at=? WHERE id=?',[$p['title'],$p['eyebrow'],$p['summary'],$p['body'],$p['image'],$p['seo'],$p['status'],gmdate('Y-m-d H:i:s'),$r['page_id']]);
    db()->commit();
    output(['ok'=>true]);
}
if($action==='savePost'){
    $status=text($data,'status',20,true);
    if(!in_array($status,['published','draft'],true))fail('Invalid status.');
    $id=(int)($data['id']??0);
    $old=$id?query('SELECT * FROM posts WHERE id=?',[$id])->fetch():null;
    if($id&&!$old)fail('Post not found.',404);
    $published=$status==='published'?($old['published_at']??gmdate('Y-m-d H:i:s')):null;
    $values=[text($data,'title',200,true),text($data,'summary',1000),text($data,'body',30000,true),valid_image(text($data,'image',500)),$status,$published,gmdate('Y-m-d H:i:s')];
    if($id){
        $values[]=$id;
        query('UPDATE posts SET title=?,summary=?,body=?,image=?,status=?,published_at=?,updated_at=? WHERE id=?',$values);
    }
    else query('INSERT INTO posts (title,summary,body,image,status,published_at,updated_at) VALUES (?,?,?,?,?,?,?)',$values);
    output(['ok'=>true]);
}
if($action==='deletePost'){
    query('DELETE FROM posts WHERE id=?',[(int)($data['id']??0)]);
    output(['ok'=>true]);
}
if($action==='enquiryStatus'){
    if(!in_array($data['status']??'',['new','progress','resolved'],true))fail('Invalid status.');
    query('UPDATE enquiries SET status=? WHERE id=?',[$data['status'],(int)($data['id']??0)]);
    output(['ok'=>true]);
}
if($action==='upload'){
    if(empty($_FILES['file'])||$_FILES['file']['error']!==UPLOAD_ERR_OK)fail('Upload failed. Check file size and PHP upload limits.');
    $f=$_FILES['file'];
    if($f['size']>8*1024*1024)fail('Maximum file size is 8 MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
    if(!isset($types[$mime]))fail('Upload a JPEG, PNG, WebP or PDF file.');
    if(str_starts_with($mime,'image/')&&!getimagesize($f['tmp_name']))fail('Invalid image.');
    $filename=bin2hex(random_bytes(16)).'.'.$types[$mime];
    $dir=$config['uploads_directory']??ROOT.'/public/uploads';
    if(!is_dir($dir)&&!mkdir($dir,0755,true))throw new RuntimeException('Upload directory is unavailable.');
    if(!move_uploaded_file($f['tmp_name'],$dir.'/'.$filename))throw new RuntimeException('Unable to save upload.');
    query('INSERT INTO media (name,url,type,created_at) VALUES (?,?,?,?)',[substr(basename($f['name']),0,200),'uploads/'.$filename,$mime,gmdate('Y-m-d H:i:s')]);
    output(['ok'=>true]);
}
