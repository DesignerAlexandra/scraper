<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/constants.php';

$dirs = scandir(STRUCT_DIR);

$serverUrl = 'http://localhost:4444';
$exceptionsDir = ['.', '..', 'images', 'links.txt', 'relation.txt'];

$group_level = 1;

$dns = '172.17.0.1';
$port = '30002';
$user = 'dev';
$db = 'DB';
$pass ='123';

$pdo = new PDO("mysql:host=$dns:$port;dbname=$db", $user, $pass);


foreach ($dirs as $dir) {

    if (substr($dir, -4) != ".csv") {

        echo "Файл заканчивается на .csv";

    } else {

        $currentPath = STRUCT_DIR . '/' . $dir;

        if(!is_dir(STRUCT_DIR . '/' . $dir)) {
            continue;
        } 

        $sqlGroup = "
            CREATE TABLE IF NOT EXISTS Groups$group_level (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(50) NOT NULL
            );
        ";

        $stmt = $pdo->prepare($sqlGroup);

        $stmt->execute();

        $sqlInsert = <<<SQL
            INSERT INTO Groups$group_level (`title`) VALUES ($dir);
        SQL;

    }

}