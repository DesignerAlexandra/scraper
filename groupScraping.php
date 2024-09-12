<?php

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/constants.php';


$dirs = scandir(STRUCT_DIR);

foreach ($dirs as $dir) {
    
    if(!in_array($dir, ['.', '..'])) {

        if(!is_dir(STRUCT_DIR . '/' . $dir)) {
            continue;
        }

        $files = scandir(STRUCT_DIR . '/' . $dir);

        foreach ($files as $file) {
            if($file == 'links.txt') {

                $stream = fopen(STRUCT_DIR . '/' . $dir . '/' . $file, 'r');

                while ($row = fgets($stream)) {
                    
                    $link = trim($row);

                    $driver = RemoteWebDriver::create('http://localhost:4444/', DesiredCapabilities::firefox());

                    $driver->get($link);
                    // Получаю элементы групп
                    try {

                        $productCardsList = $driver->findElement(WebDriverBy::className('product-cards__list'));

                        $productCardsItems = $productCardsList->findElements(WebDriverBy::className('product-cards__item'));

                    } catch (NoSuchElementException $e) {

                        $driver->quit();

                        die('No such element product-cards__list, product-cards__item');

                    }
                    // Получаю ссылки, картинки, названия крточек и создаю дерриктории внутри которых создаю папку с картинками
                    // файл с сылками, файл с названиями зависимостей подгрупп
                    foreach ($productCardsItems as $productCardItem) {
                        
                        try {

                            $img = $productCardItem->findElement(WebDriverBy::tagName('img'));

                            $linkP = $productCardItem->findElement(WebDriverBy::className('product-cards__title'));

                            $link = $linkP->findElement(WebDriverBy::tagName('a'));

                        } catch (NoSuchElementException $e) {
                           
                            $driver->quit();

                            die('No such element img, product-cards__title, a');

                        }

                        $nameGroup = removeQuotes(replaceSpace($link->getText()));
                        $hrefSubGroup = URL_SMARTA . trim($link->getAttribute('href'));

                        if(!is_dir(STRUCT_DIR . '/' . $dir . '/' . $nameGroup)) {

                            mkdir(STRUCT_DIR . '/' . $dir . '/' . $nameGroup, 0700);
                    
                        } 
    
                        if(!is_dir(STRUCT_DIR . '/' . $dir . '/' . $nameGroup . '/images')) { 

                            mkdir(STRUCT_DIR . '/' . $dir . '/' . $nameGroup . '/images', 0700);

                        }

                        $newDir = STRUCT_DIR . '/' . $dir . '/' . $nameGroup;
                        $newImgDir = STRUCT_DIR . '/' . $dir . '/' . $nameGroup . '/images';

                        $imgSrc = $img->getAttribute('src');

                        $newImageName = fileRenamer($imgSrc, $nameGroup);

                        if(is_dir($newImgDir)) {

                            file_put_contents($newImgDir .'/'. $newImageName, file_get_contents(URL_SMARTA . $imgSrc));
                    
                        } else {
                    
                            $driver->quit();
                    
                            die('Dir is not found');
                    
                        }

                        file_put_contents($newDir . '/relation.txt', $nameGroup . "\n", FILE_APPEND);
                        file_put_contents($newDir . '/links.txt', $hrefSubGroup . "\n", FILE_APPEND);
                    }

                    $driver->quit();
                }
            }
        }
    }
}

$driver->quit();