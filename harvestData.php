<?php

$struct = __DIR__ . '/struct';

function harvester($path)
{
    $currentDirs = scandir($path);

    foreach ($currentDirs as $file) {

        if(in_array($file, ['.', '..'])) continue;
        if(strpos($file, '.csv')) {
            $currentFile = $path . '/' . $file;
            file_put_contents(__DIR__ . '/data' . "/$file", file_get_contents($currentFile));
            // unlink($currentFile);
        }

        $currentPath = $path . '/' . $file;

        if(is_dir($currentPath)) {
            harvester($currentPath);
        }

    }
}


harvester($struct);