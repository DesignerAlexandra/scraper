<?php

$files = scandir(__DIR__ . '/data');

$dns = '172.17.0.1';
$port = '30002';
$user = 'dev';
$db = 'DB';
$pass ='123';

$pdo = new \PDO("mysql:host=$dns:$port;dbname=$db", $user, $pass);

foreach ($files as $file) {
    
    if(in_array($file, ['.', '..'])) continue;

    $stream = fopen(__DIR__ . '/data' . "/$file", 'r');

    $title = replaceS(removeExtensionName($file));

    while($row = fgetcsv($stream, null, ';')) {

        if($row[0] == 'headers') continue;

        $code = $row[0];
        $count = (int)preg_replace("/[^0-9]/", "", $row[count($row) - 1]);
        $price = $row[count($row) - 2] == '' ? '0' : $row[count($row) - 2];

        $createCodes = "CREATE TABLE IF NOT EXISTS codes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(20) NOT NULL,
            count INT DEFAULT NULL,
            price FLOAT DEFAULT 0.00,
            product_id INT NOT NULL,
            FOREIGN KEY (product_id) REFERENCES products (id)
            );
        ";

        pdoInit($pdo, $createCodes);

        $sqlGetIdProduct = "
        SELECT id FROM products WHERE title = '$title';
        ";

        $id = getParentId($pdo, $sqlGetIdProduct);

        $sqlInsertCode = "INSERT INTO codes (
            title, count, price, product_id
            ) VALUES ( :title, :count, :price, :product_id
            );  
        ";

        $data = [
            'title' => $code,
            'count' => $count,
            'price' => $price,
            'product_id' => $id
        ];

        pdoInsert($pdo, $sqlInsertCode, $data);
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

function pdoInsert($pdo, $query, $data)
{
    $stmt = $pdo->prepare($query);
        
    $stmt->execute($data);   
}

function removeExtensionName($fileName): string
{
    $parts = explode(".", $fileName);
    $fileNameWithoutExtension = $parts[0];
    return $fileNameWithoutExtension;
}