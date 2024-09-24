<?php

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/constants.php';

$serverUrl = 'http://localhost:4444';

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

    $exceptionsDir = ['.', '..', 'images', 'links.txt', 'relation.txt'];


    foreach ($dirs as $file) {

        $checkType = false;

        $currentPath = $path . '/' . $file;

        if (is_dir($currentPath)) {

            $currentDir = scandir($currentPath);

            foreach ($currentDir as $currentFile) {

                if(in_array($currentFile, $exceptionsDir)) continue;
        
                if (substr($currentFile, -4) == ".csv") $checkType = true;
        
            }
        }


        if(in_array($file, $exceptionsDir)) continue;

        if (substr($file, -4) != ".csv") {

            if($checkType) {

                $sqlCreateProducts = "
                    CREATE TABLE IF NOT EXISTS products (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(100) NOT NULL
                    );
                ";

                pdoInit($connect, $sqlCreateProducts);

                $title = replaceS(removeExtensionName($file));
                $data = [':title' => $title];

                $sqlInsertProduct = "
                    INSERT INTO products (`title`) VALUES (:title);
                ";

                pdoIsert($connect, $sqlInsertProduct, $data);

                $parentGroupLevel = $groupLevel - 1;
                $sqlCreateGroupProduct = "
                    CREATE TABLE IF NOT EXISTS group{$parentGroupLevel}_product (
                    product_id INT,
                    FOREIGN KEY (product_id) REFERENCES products (id),
                    group{$parentGroupLevel}_id INT,
                    FOREIGN KEY (group{$parentGroupLevel}_id) REFERENCES groups$parentGroupLevel (id),
                    UNIQUE(product_id, group{$parentGroupLevel}_id)
                    );
                ";

                pdoInit($connect, $sqlCreateGroupProduct);

                $sqlGetIdProduct = "
                    SELECT id FROM products WHERE title = '$title';
                ";

                $productId = getParentId($connect, $sqlGetIdProduct);

                $sqlGetIdGroup = "
                    SELECT id FROM groups$parentGroupLevel WHERE title = '$parentDirName';
                ";

                $groupId = getParentId($connect, $sqlGetIdGroup);

                $sqlInsertGroupProduct = "
                    INSERT INTO group{$parentGroupLevel}_product (product_id, group{$parentGroupLevel}_id) VALUES (:product_id, :group_id);
                ";

                $data = [
                    ':product_id' => $productId,
                    ':group_id' => $groupId
                ];

                try {
                    pdoIsert($connect, $sqlInsertGroupProduct, $data);
                } catch (\PDOException $e) {
                    var_dump($e->getMessage());
                }

            } else {
    
                if(!is_dir($path . '/' . $file)) {

                    continue;
                } 

                if($groupLevel == 1) {

                    $sqlCreateGroup = "
                    CREATE TABLE IF NOT EXISTS groups$groupLevel (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(100) NOT NULL
                    );
                    ";

                    pdoInit($connect, $sqlCreateGroup);

                    $title = replaceS($file);

                    $sqlInsert = "INSERT INTO groups$groupLevel (`title`) VALUES ('$title');";

                    pdoInit($connect, $sqlInsert);

                } else {
                    $parentGroupLevel = $groupLevel - 1;

                    $sqlCreateGroup = "CREATE TABLE IF NOT EXISTS groups$groupLevel (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(100) NOT NULL,
                    group{$parentGroupLevel}_id INT,
                    FOREIGN KEY (group{$parentGroupLevel}_id) REFERENCES groups$parentGroupLevel (id)
                    );";

                    pdoInit($connect, $sqlCreateGroup);

                    $sqlParentGroup = "SELECT `id` FROM groups$parentGroupLevel WHERE `title` = '$parentDirName';";

                    $id = getParentId($connect, $sqlParentGroup);

                    $title = replaceS($file);

                    $sqlInsert = "INSERT INTO groups$groupLevel (`title`, group{$parentGroupLevel}_id) VALUES ('$title', $id);";

                    pdoInit($connect, $sqlInsert);
                }

            }
    
        } else {
    
            $createCodes = "CREATE TABLE IF NOT EXISTS codes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(50) NOT NULL,
                count INT DEFAULT NULL,
                price VARCHAR(20) NOT NULL,
                product_id INT NOT NULL,
                FOREIGN KEY (product_id) REFERENCES products (id)
                );
            ";

            pdoInit($connect, $createCodes);

            $productTitle = replaceS(removeExtensionName($file));
    
            $sqlGetIdProduct = "
                    SELECT id FROM products WHERE title = '$productTitle';
                ";

            $productId = getParentId($connect, $sqlGetIdProduct);

            return;

        }


        runner($currentPath, $connect, $groupLevel + 1, $title);

    }

}

function replaceS($str)
{
    return preg_replace("/_+/", " ", $str);
}

function pdoInit($pdo, $query)
{
    $stmt = $pdo->prepare($query);
        
    $stmt->execute();   
}

function getParentId($pdo, $query)
{
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['id'];
}

function pdoIsert($pdo, $query, $data)
{
    $stmt = $pdo->prepare($query);
        
    $stmt->execute($data);   
}

runner(STRUCT_DIR, $pdo);