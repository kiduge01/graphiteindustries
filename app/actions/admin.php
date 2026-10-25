<?php
declare(strict_types=1);
if ($action === 'updateUser') {
    require_user(true);
    $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
    $role = text($data, 'role', 20, true);
    $active = $data['active'] ?? null;
    if (!$id || !in_array($role, ['admin', 'editor'], true) || !is_bool($active)) {
        fail('Check the account, role and status.');
    }
    if ($id === (int)$user['id'] && (!$active || $role !== 'admin')) {
        fail('You cannot disable or demote your own account.');
    }
    db()->beginTransaction();
    // Serialise account changes, including concurrent attempts to demote admins.
    $lock = db()->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql' ? ' FOR UPDATE' : '';
    all("SELECT id FROM users WHERE role='admin' AND active=1" . $lock);
    $target = query('SELECT * FROM users WHERE id=?' . $lock, [$id])->fetch();
    if (!$target) {
        db()->rollBack();
        fail('Account not found.', 404);
    }
    if ($target['role'] === 'admin' && (int)$target['active'] === 1 && (!$active || $role !== 'admin') &&
    (int)query("SELECT COUNT(*) FROM users WHERE role='admin' AND active=1")->fetchColumn() <= 1) {
        db()->rollBack();
        fail('Keep at least one active administrator.');
    }
    query('UPDATE users SET role=?,active=?,session_version=session_version+1 WHERE id=?', [$role, $active ? 1 : 0, $id]);
    db()->commit();
    output(['ok' => true]);
}
if($action==='settings'){
    require_user(true);
    $allowed=['company','email','phone','address','tagline'];
    if(!filter_var($data['email']??'',FILTER_VALIDATE_EMAIL))fail('Enter a valid email.');
    db()->beginTransaction();
    foreach($allowed as $key){
        query('UPDATE settings SET setting_value=? WHERE setting_key=?',[text($data,$key,500,true),$key]);
    }
    db()->commit();
    output(['ok'=>true]);
}
if($action==='saveUser'){
    require_user(true);
    $role=text($data,'role',20,true);
    $email=text($data,'email',190,true);
    $pass=text($data,'password',1000,true);
    if(!in_array($role,['admin','editor'],true)||strlen($pass)<12||!filter_var($email,FILTER_VALIDATE_EMAIL))fail('Check the role, email and password (12 characters minimum).');
    if(query('SELECT id FROM users WHERE email=?',[$email])->fetch())fail('This email is already registered.');
    query('INSERT INTO users (name,email,password_hash,role,created_at) VALUES (?,?,?,?,?)',[text($data,'name',120,true),$email,password_hash($pass,PASSWORD_DEFAULT),$role,gmdate('Y-m-d H:i:s')]);
    output(['ok'=>true]);
}
