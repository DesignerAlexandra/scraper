<?php

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
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

                        $breadCrambs = $driver->findElement(WebDriverBy::className('breadcrumbs__list'));

                        $breadCrambsLi = $breadCrambs->findElements(WebDriverBy::className('breadcrumbs__item'));

                        $productCards = $driver->findElement(WebDriverBy::className('product-cards'));

                        $productCardsLists = $productCards->findElements(WebDriverBy::className('product-cards__list'));

                        $active = 0;
                        $classes = [];
                        foreach ($productCardsLists as $key => $productCardList) {
                            
                            $classes = explode(' ', $productCardList->getAttribute('class'));

                            if(!in_array('side-menu__content-item--none-active', $classes)) {
                                
                                $active = $key;
                                
                            }

                        }

                        $productCardsItems = $productCardsLists[$active]->findElements(WebDriverBy::className('product-cards__item'));

                        
                    } catch (NoSuchElementException $e) {

                        $driver->quit();

                        die('No such element product-cards__list, product-cards__item');

                    }
                    // Получаю ссылки, картинки, названия крточек и создаю дерриктории внутри которых создаю папку с картинками
                    // файл с сылками, файл с названиями зависимостей подгрупп
                    foreach ($productCardsItems as $productCardItem) {
                        
                        try {

                            $imgLink = $productCardItem->findElement(WebDriverBy::className('product-cards__image'));

                            $img = $imgLink->findElement(WebDriverBy::tagName('img'));

                        } catch (NoSuchElementException $e) {
                           
                            $driver->quit();

                            die('No such element img, product-cards__title, a');

                        }

                        $nameGroup = replaceSlash(removeQuotes(replaceSpace($breadCrambsLi[count($breadCrambsLi) - 1]->getText())));

                        $relation = replaceSlash(removeQuotes(replaceSpace($img->getAttribute('alt'))));

                        $hrefSubGroup = URL_SMARTA . trim($imgLink->getAttribute('href'));

                        if(!checkUrl($hrefSubGroup)) {

                            continue;
                        } 

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

                        file_put_contents($newDir . '/relation.txt', $relation . "\n", FILE_APPEND);
                        if(checkUrl($hrefSubGroup)) {
                            file_put_contents($newDir . '/links.txt', $hrefSubGroup . "\n", FILE_APPEND);
                        }
                    }

                    $driver->quit();
                }
            }
        }
    }
}

$driver->quit();