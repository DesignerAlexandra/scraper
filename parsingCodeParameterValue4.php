<?php

require_once __DIR__ . '/constants.php';

$dns = DNS;
$port = PORT;
$user = USER;
$db = DB;
$pass = PASS;

$pdo = new \PDO("mysql:host=$dns:$port;dbname=$db", $user, $pass);

$files = scandir(__DIR__ . '/data');

$createCodes = "CREATE TABLE IF NOT EXISTS code_parameter_value (
    code_id INT NOT NULL,
    FOREIGN KEY (code_id) REFERENCES codes (id),
    parameter_id INT NOT NULL,
    FOREIGN KEY (parameter_id) REFERENCES parameters (id),
    value_id INT NOT NULL,
    FOREIGN KEY (value_id) REFERENCES parameter_values (id)
    );
";

$stmt = $pdo->prepare($createCodes);
$stmt->execute();

$data = [];
foreach ($files as $file) {
    if(in_array($file, ['.', '..'])) {
        continue;
    }

        $stream = fopen(__DIR__ . "/data/$file", 'r');

        while ($row = fgetcsv($stream, null, ';')) {
            if($row[0] == 'headers') {
                $headers = [];
                foreach ($row as $key => $header) {
                    if($key != 0 && $key != 1 && $key != count($row) - 1 && $key != count($row) - 2 && $key != count($row) - 3) {
                        $headers[$key - 1] = $header;
                    }
                }
            } else {
                $values = [];
                foreach ($row as $key => $value) {
                    if($key != count($row) - 1 && $key != count($row) - 2 && $key != count($row) - 3) {
                        if($key == 0) {
                            $data[$value] = [];
                        } else {
                            $data[$row[0]][] = [$headers[$key], $value];
                            $values[] = $value;
                        }
                    }
                }
            }
        }   
}

$queryGetPramId = <<<SQL
    SELECT (`id`) FROM parameters WHERE `title` = :title
SQL;
$queryGetPoductId = <<<SQL
    SELECT (`id`) FROM codes WHERE `title` = :title
SQL;
$queryGetValueId = <<<SQL
    SELECT (`id`) FROM parameter_values WHERE `title` = :title
SQL;
$queryInsertRelation = <<<SQL
    INSERT INTO code_parameter_value (`code_id`, `parameter_id`, `value_id`) VALUES (:code_id, :parameter_id, :value_id)
SQL;
$i = 0;
$streamError = fopen(__DIR__ . '/err.csv', 'a');
foreach ($data as $productName => $params) {
    $i++;
    $stmtProduct = $pdo->prepare($queryGetPoductId);
    $stmtProduct->execute([
        ':title' => $productName
    ]);

    $productId = $stmtProduct->fetchAll(PDO::FETCH_ASSOC)[0]['id'];


    foreach ($params as $key => $param) {

        $stmtParam = $pdo->prepare($queryGetPramId);
        $stmtParam->execute([
            ':title' => $param[0]
        ]);
        $paramId = $stmtParam->fetchAll(PDO::FETCH_ASSOC)[0]['id'];

        $stmtValue = $pdo->prepare($queryGetValueId);
        $stmtValue->execute([
            ':title' => $param[1]
        ]);

        $valueId = $stmtValue->fetchAll(PDO::FETCH_ASSOC)[0]['id'];

        if(!$valueId) {
            fputcsv($streamError, [$param[1], $paramId], ';');
            continue;
        }

        $stmt = $pdo->prepare($queryInsertRelation);
        $stmt->execute([
            ':code_id' => $productId,
            ':parameter_id' => $paramId,
            ':value_id' => $valueId
        ]);
    }

}