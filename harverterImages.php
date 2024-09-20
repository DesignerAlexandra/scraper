<?php

$struct = __DIR__ . '/struct';

function harvester($path)
{
    $currentDirs = scandir($path);

    foreach ($currentDirs as $file) {

        if(in_array($file, ['.', '..'])) continue;

        if(strpos($file, '.jpg')) {
            $currentImg = $path . '/' . $file;
            file_put_contents(__DIR__ . '/images' . "/$file", file_get_contents($currentImg));
        }

        $currentPath = $path . '/' . $file;

        if(is_dir($currentPath)) {
            harvester($currentPath);
        }

    }
}

harvester($struct);