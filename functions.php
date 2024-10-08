<?php

function fileRenamer($src, $newName): string
{
    $fileExtension = pathinfo($src, PATHINFO_EXTENSION);

    return $newName .'.'. $fileExtension;
}

function checkUrl($str): bool
{
    $base = basename($str);

    if(strpos($base, '.pdf') !== false) {
        return false;
    }

    return true;
}

function replaceSpace($str) {
    return preg_replace('/ +/', '_', $str);
}

function replaceUnderscores($str) {
    return preg_replace('/_+/', '_', $str);
}

function removeLastFour($str) {
    return substr($str, 0, -4);
}

function removeQuotes($str) {
    return str_replace('"', '', $str);
}

function replaceSlash($str) {
    return str_replace("/", "_", $str);
}

function uniqueUrl($path) {
    $stream = fopen($path, 'r');
    $arr = [];
    while ($row = fgets($stream)) {
        if(!in_array(trim($row),  $arr)) {
            $arr[] = trim($row);
        }
    }

    fclose($stream);
    unlink($path);

    $stream = fopen($path, 'w');

    foreach ($arr as $value) {
        fputs($stream, $value . "\n");
    }
}

function removeExtensionName($fileName): string
{
    $parts = explode(".", $fileName);
    $fileNameWithoutExtension = $parts[0];
    return $fileNameWithoutExtension;
}