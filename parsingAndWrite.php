<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/constants.php';

$serverUrl = 'http://localhost:4444';
$exceptionsDir = ['.', '..', 'images', 'links.txt', 'relation.txt'];

$group_level = 1;

$dns = '172.17.0.1';
$port = '30002';
$user = 'dev';
$db = 'DB';
$pass ='123';

$pdo = new \PDO("mysql:host=$dns:$port;dbname=$db", $user, $pass);

function runner($path, \PDO $connect, $groupLevel = 1, $parentDirName = '')
{
    $dirs = scandir($path);

    foreach ($dirs as $file) {

        if(in_array($file, ['.', '..'])) continue;

        if (substr($path, -4) != ".csv") {
    
            echo "Файл заканчивается на .csv";
    
        } else {
    
            $currentPath = $path . '/' . $file;
    
            if(!is_dir($path . '/' . $file)) {
                continue;
            } 

            if($groupLevel == 1) {

                $sqlGroup = "
                CREATE TABLE IF NOT EXISTS Groups$groupLevel (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(50) NOT NULL
                );
                ";
    
                $stmt = $connect->prepare($sqlGroup);
        
                $stmt->execute();

                $title = replaceSlash($file);

                $sqlInsert = <<<SQL
                    INSERT INTO Groups$groupLevel (`title`) VALUES ($title);
                SQL;

                $stmt = $connect->prepare($sqlInsert);

                $stmt->execute();

            } else {
                $parentGroupLevl = $groupLevel - 1;
                $sqlParentGroup = <<<SQL
                    SELECT id FROM Groups$parentGroupLevl where title = $parentDirName;
                SQL;

                $stmt = $connect->prepare($sqlParentGroup);
                $stmt->execute();
                $id = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                $sqlGroup = "
                CREATE TABLE IF NOT EXISTS Groups$groupLevel (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(50) NOT NULL,
                group{$parentDirName}_id INT,
                FOREIGN KEY (group{$parentDirName}_id) Groups$parentGroupLevl REFERENCES  (id)
                );
                ";

            }
    
        }

        runner($currentPath, $connect, $groupLevel + 1, $title);
    
    }
}

function replaceSlash($str)
{
    return str_replace("\_+", " ", $str);
}