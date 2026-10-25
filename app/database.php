<?php
declare(strict_types=1);
function db(): PDO {
    global $config;
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO($config['dsn'], $config['db_user'] ?? '', $config['db_password'] ?? '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
        if (str_starts_with($config['dsn'],'sqlite:')) {
            $pdo->exec('PRAGMA foreign_keys=ON');
        }
    }
    return $pdo;
}
function query(string $sql, array $params=[]): PDOStatement {
    $s=db()->prepare($sql);
    $s->execute($params);
    return $s;
}
function all(string $sql, array $params=[]): array {
    return query($sql,$params)->fetchAll();
}
