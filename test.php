<?php


$dns = '172.17.0.1';
$port = '30002';
$user = 'dev';
$db = 'DB';
$pass ='123';

$pdo = new \PDO("mysql:host=$dns:$port;dbname=$db", $user, $pass);

$sql = <<<SQL
    select parameters.title as parameterName, parameter_values.title as valueName
    FROM parameter_value
    JOIN parameters ON parameters.id = parameter_value.parameter_id
    JOIN parameter_values ON parameter_values.id = parameter_value.value_id;
SQL;

$stmt = $pdo->prepare($sql);

$stmt->execute();

$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

$data = [];

foreach ($result as $params) {
    $data[$params['parameterName']][] = $params['valueName'];
}

$keys = array_keys($data);

$maxCountAttr = 0;
foreach ($data as $parameters) {
    if($maxCountAttr < count($parameters)) {
        $maxCountAttr = count($parameters);
    }
}

$stream = fopen(__DIR__ . "/data.csv", 'w');

fputcsv($stream, $keys, ";");
for ($i=0; $i < $maxCountAttr; $i++) { 
    $row = [];
    foreach ($keys as $param) {
        $row[$param] = $data[$param][$i] ?: '';
    }
    fputcsv($stream, $row, ';');
}