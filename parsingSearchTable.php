<?php

$pdo = new PDO('mysql:host=172.17.0.1;port=30002;dbname=DB', 'dev', '123');

$queryCreateSearchTable = <<<SQL
    CREATE TABLE IF NOT EXISTS search_table (
    title VARCHAR(100) NOT NULL,
    element_id INT NOT NULL,
    table_name VARCHAR(20) NOT NULL,
    KEY `title_index` (`title`)
    );
SQL;

$queryData1 = <<<SQL
    SELECT `id`, `title`
    FROM groups1
SQL;
$queryData2 = <<<SQL
    SELECT `id`, `title`
    FROM groups2
SQL;
$queryData3 = <<<SQL
    SELECT `id`, `title`
    FROM groups3
SQL;
$queryData4 = <<<SQL
    SELECT `id`, `title`
    FROM groups4
SQL;
$queryProducts = <<<SQL
    SELECT `id`, `title`
    FROM products
SQL;

$queryCodes = <<<SQL
    SELECT `id`, `title`
    FROM codes
SQL;

$queryInsert = <<<SQL
    INSERT INTO search_table (`title`, `element_id`, `table_name`) VALUES (:title, :element_id, :table_name);
SQL;

$queries = [
    'groups1' => $queryData1,
    'groups2' => $queryData2,
    'groups3' => $queryData3,
    'groups4' => $queryData4,
    'products' => $queryProducts,
    'codes' => $queryCodes
];

$stmt = $pdo->prepare($queryCreateSearchTable);

$stmt->execute();

foreach ($queries as $table => $value) {
    $stmt = $pdo->prepare($value);

    $stmt->execute();

    $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    foreach ($res as $key => $value) {
        $stmt = $pdo->prepare($queryInsert);
        $stmt->execute([
            ':title' => $value['title'],
            ':element_id' => $value['id'],
            ':table_name' => $table
        ]);
    }
}

