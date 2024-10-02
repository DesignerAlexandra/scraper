<?php
require_once __DIR__ . '/constants.php';

$files = scandir(__DIR__ . '/data');

$dns = DNS;
$port = PORT;
$user = USER;
$db = DB;
$pass = PASS;

$pdo = new \PDO("mysql:host=$dns:$port;dbname=$db", $user, $pass);

$createCodes = "CREATE TABLE IF NOT EXISTS codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(20) NOT NULL,
    count INT DEFAULT NULL,
    price FLOAT DEFAULT 0.00,
    name VARCHAR(150) NOT NULL,
    product_id INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products (id)
        );
";

$sqlInsertCode = "INSERT INTO codes (
    title, count, price, name, product_id
    ) VALUES ( :title, :count, :price, :name, :product_id );  
";

foreach ($files as $file) {
    
    if(in_array($file, ['.', '..'])) continue;

    $stream = fopen(__DIR__ . '/data' . "/$file", 'r');

    $title = replaceS(removeExtensionName($file));

    $sqlGetIdProduct = "
        SELECT id FROM products WHERE title = '$title';
    ";

    while($row = fgetcsv($stream, null, ';')) {

        if($row[0] == 'headers') continue;

        $code = $row[0];
        $count = (int)preg_replace("/[^0-9]/", "", $row[count($row) - 1]);
        $price = $row[count($row) - 2] == '' ? '0' : $row[count($row) - 2];
        $name = $row[count($row) - 3];

        pdoInit($pdo, $createCodes);

        $id = getParentId($pdo, $sqlGetIdProduct);

        $data = [
            'title' => $code,
            'count' => $count,
            'price' => $price,
            'name' => $name,
            'product_id' => $id
        ];

        try {
            pdoInsert($pdo, $sqlInsertCode, $data);
        } catch (\PDOException $th) {
            continue;
        }
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