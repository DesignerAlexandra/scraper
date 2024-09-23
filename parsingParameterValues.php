<?php

$pdo = new PDO('mysql:host=172.17.0.1;port=30002;dbname=DB', 'dev', '123');

$files = scandir(__DIR__ . '/data');

$sqlCreateParametes = "CREATE TABLE IF NOT EXISTS parameters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL
    );
";
$sqlCreateValues = "CREATE TABLE IF NOT EXISTS parameter_values (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title TEXT NOT NULL
    );
";

$stmt = $pdo->prepare($sqlCreateParametes);
$stmt->execute();
$stmt = $pdo->prepare($sqlCreateValues);
$stmt->execute();

$params = [];
foreach ($files as $file) {
    if(!in_array($file, ['.', '..'])) {

        $stream = fopen(__DIR__ . "/data/$file", 'r');

        while ($row = fgetcsv($stream, null, ';')) {
            if($row[0] == 'headers') {
                $headers = [];
                foreach ($row as $key => $value) {
                    if($key != 0 && $key != 1 && $key != count($row) - 1 && $key != count($row) - 2) {
                        if(!array_key_exists($value, $params)) {
                            $params[$value] = [];
                        }
                    $headers[$key] = $value;  
                    }
                }
            } else {
                foreach ($row as $key => $value) {
                    if($key != 0 && $key != count($row) - 1 && $key != count($row) - 2) {
                        if(!in_array($value, $params[$headers[$key+1]])) {
                            $params[$headers[$key+1]][] = $value;
                        }
                    }
                }

            }
        }

    }
}


$queryGetId = <<<SQL
    SELECT (`id`) FROM parameters WHERE `title` = :title
SQL;

$queryGetIdValue = <<<SQL
    SELECT (`id`) FROM parameter_values WHERE title = :title;
SQL;
$queryGetIdParameter = <<<SQL
    SELECT (`id`) FROM parameters WHERE title = :title;
SQL;

$createParameterValue = <<<SQL
    CREATE TABLE IF NOT EXISTS parameter_value (
            parameter_id INT NOT NULL,
            FOREIGN KEY (parameter_id) REFERENCES parameters (id),
            value_id INT NOT NULL,
            FOREIGN KEY (value_id) REFERENCES parameter_values (id)
        );
SQL;

$insertParameterValue = <<<SQL
    INSERT INTO parameter_value VALUES (:parameter_id, :value_id);
SQL;


// $queryInsertValues = <<<SQL
//     INSERT INTO parameter_values (`title`) VALUES (:title)
// SQL;

// $queryInsertParam = <<<SQL
//     INSERT INTO parameters (title) VALUES (:title);
// SQL;


// foreach ($params as $key => $value) {
//     $stmt = $pdo->prepare($queryInsertParam);
//     $stmt->execute([
//         'title' => $key
//     ]);
// }

$stmt = $pdo->prepare($createParameterValue);
$stmt->execute();


foreach ($params as $param => $values) {
    $stmt = $pdo->prepare($queryGetId);
    $stmt->execute([
        ':title' => $param
    ]);
    $paramId = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['id'];

    foreach ($values as $key => $value) {
        // $stmt = $pdo->prepare($queryInsertValues);
        // $stmt->execute([
        //     ':title' => $value
        // ]);

        $stmt = $pdo->prepare($queryGetIdValue);
        $stmt->execute([
             ':title' => $value
        ]);
        $valieId = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0]['id'];

        $stmt = $pdo->prepare($insertParameterValue);
        $stmt->execute([
            'parameter_id' => $paramId,
            'value_id' => $valieId
        ]);
    }
}