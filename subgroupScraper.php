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

$serverUrl = 'http://localhost:4444';
$exceptionsDir = ['.', '..', 'images', 'links.txt', 'relation.txt'];

foreach ($dirs as $dir) {
    
    if(in_array($dir, $exceptionsDir)) {
        continue;
    }

    $categories = scandir(STRUCT_DIR . '/' . $dir);

    foreach ($categories as $group) {
        
        if(in_array($group, $exceptionsDir)) {
            continue;
        }

        $subgroups = scandir(STRUCT_DIR . '/' . $dir . '/' . $group);

        foreach ($subgroups as $subgroup) {
            
            if($subgroup != 'links.txt') {
                continue;
            }

            $stream = fopen(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $subgroup, 'r');

            while ($row = fgets($stream)) {
                
                $checkElems = false;
                $offset = 1;
                $link = trim($row);
                    var_dump($link);
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

                        $checkElems = true;

                    }
                    // *******************************************************************************
                    // В случае если элементы карточек отсутствуют проверяю наличие таблицы продуктов

                    if($checkElems) {

                        try {
                            
                            $breadCrambs = $driver->findElement(WebDriverBy::className('breadcrumbs__list'));

                            $breadCrambsLi = $breadCrambs->findElements(WebDriverBy::className('breadcrumbs__item'));



                        } catch (NoSuchElementException $e) {
                            
                            $driver->quit();
                            
                            die();
                            
                        }

                        $nameGroup = replaceSlash(removeQuotes(replaceSpace($breadCrambsLi[count($breadCrambsLi) - 1]->getText())));

                        if(!is_dir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup)) {

                            mkdir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup, 0700);
                    
                        } 
                        if(!is_dir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup . '/images')) { 

                            mkdir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup . '/images', 0700);

                        }

                        $newDir = STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup;
                        $newImgDir = STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup . '/images';

                        try {
                            
                            $image = $driver->findElement(WebDriverBy::className('catalog-section__image'));

                        } catch (NoSuchElementException $e) {
                            
                            $driver->quit();

                            die('No such image for product');

                        }

                        if(is_dir($newImgDir)) {

                            $imgSrc = $image->getAttribute('src');

                            $newImageName = fileRenamer($imgSrc, $nameGroup);

                            file_put_contents($newImgDir .'/'. $newImageName, file_get_contents(URL_SMARTA . $imgSrc));
                    
                        } else {
                    
                            $driver->quit();
                    
                            die('Dir is not found');
                    
                        }

                        $driver->quit();
                        clicker(RemoteWebDriver::create($serverUrl, DesiredCapabilities::firefox()), $link, $newDir, $offset, 1);


                        continue;
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
                        if(!is_dir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup)) {

                            mkdir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup, 0700);
                    
                        } 
                        if(!is_dir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup . '/images')) { 

                            mkdir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup . '/images', 0700);

                        }

                        $newDir = STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup;
                        $newImgDir = STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $nameGroup . '/images';

                        $imgSrc = $img->getAttribute('src');

                        $newImageName = fileRenamer($imgSrc, $nameGroup);

                        if(is_dir($newImgDir)) {

                            file_put_contents($newImgDir .'/'. $newImageName, file_get_contents(URL_SMARTA . $imgSrc));
                    
                        } else {
                    
                            $driver->quit();
                    
                            die('Dir is not found');
                    
                        }

                        
                        if(checkUrl($hrefSubGroup)) {
                            file_put_contents($newDir . '/relation.txt', $relation . "\n", FILE_APPEND);
                            file_put_contents($newDir . '/links.txt', $hrefSubGroup . "\n", FILE_APPEND);
                        }
                    }

                    $driver->quit();

            }

        }

    }
}

function clicker($driver, $url, $path, $page, $exitCheck)
{

    $driver->navigate()->to($url . "?PAGEN_1=$page");
    var_dump($url . "?PAGEN_1=$page");
    $chekBtn = false;

    if(($exitCheck + 1) < $page - 1) {
        $driver->quit();
        var_dump('Последняя страница каталога');
        return;
    }



    try {
        $title = $driver->findElement(WebDriverBy::cssSelector('.catalog-section__title'));
        $newTitle = preg_replace('/\/|\\|\[!@#$%^&.,<> * ()\]/', '_', $title->getText());
        $newTitle = replaceSlash(removeQuotes(replaceSpace($newTitle)));
        var_dump($newTitle);
    } catch (\Throwable $th) {
        var_dump('Заголовок не найден');
    }

    try {
        $btn = $driver->findElement(WebDriverBy::cssSelector('.table__pagination-btn'));
        var_dump('Пагинация найдена');

    } catch (NoSuchElementException $e) {
        var_dump('Пагинация не найдена: true');
        $chekBtn = true;
    }
    
    try {

        // $orderTable = $driver->findElement(WebDriverBy::cssSelector('.catalog-list.catalog-list--margin'));
        $orderblocks = $driver->findElements(WebDriverBy::xpath('//*[@data-entity="item"]'));
        var_dump('Таблица найдена');

        foreach ($orderblocks as $orderblock) {       
            $orderCoding = $orderblock->findElement(WebDriverBy::cssSelector('h3.catalog-list__item-title-group'));
            $orderCode = $orderCoding->findElement(WebDriverBy::cssSelector('a.catalog-list__item-title'))->getAttribute('href');
            file_put_contents($path. '/' .$newTitle.'.txt', 'http://smarta.ru'.$orderCode . "\n", FILE_APPEND);
        }

        
        if(count($orderblocks) != 0) {
            uniqueUrl($path. '/' .$newTitle.'.txt');
        }


    } catch (NoSuchElementException $e) {
        $driver->quit();
        var_dump('Таблица не найдена');
        var_dump($e->getMessage());
        return;
    }


    if($chekBtn) {
        $driver->quit();
        var_dump('Пагинация не найдена');
        return;
    }

    if(!$chekBtn) {
        $btn->click();
        $newUrl = $driver->getCurrentURL();

        $newUrl = str_replace("&", "?", $newUrl);
        $dataUrl = parse_url($newUrl);
        $queryPage = $dataUrl['query'];
        $equal_pos = strpos($queryPage, "=");
        $currentPage = (int)substr($queryPage, $equal_pos + 1);
    }

    $newPage = $page + 1;
    $driver->quit();
    clicker(RemoteWebDriver::create('http://localhost:4444', DesiredCapabilities::firefox()), $url, $path, $newPage, $currentPage);
}