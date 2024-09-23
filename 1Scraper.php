<?php

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/constants.php';



$driver = RemoteWebDriver::create('http://localhost:4444/', DesiredCapabilities::firefox());

$driver->get(URL_CATALOG);





try {

    $blockLinks = $driver->findElement(WebDriverBy::className('catalog'));

    $items = $blockLinks->findElements(WebDriverBy::className('catalog__item'));
    
} catch (NoSuchElementException $e) {
    
    $driver->quit();
    
    die("No such element catalog or catalog's item");

}
    // Получение названий каталогов и картинок
foreach ($items as $item) {
    
    try {

        $img = $item->findElement(WebDriverBy::tagName('img'));

        $headerCatalog = $item->findElement(WebDriverBy::tagName('h2'));

        $linksList = $item->findElement(WebDriverBy::className('catalog__sublist'));

        $blockLinks = $linksList->findElements(WebDriverBy::className('catalog__subitem'));

    } catch (NoSuchElementException $e) {
        
        $driver->quit();

        die("No such element item's");
    }
    // Создание ветвей директорий с иминами каталогов и сохранением в них изображений предсталений каталогов
    $catalogName = $headerCatalog->getText();

    $nameDir = removeQuotes(replaceSpace($catalogName));
        
    mkdir(STRUCT_DIR . '/' . $nameDir, 0700);

    mkdir(STRUCT_DIR . '/' . $nameDir . '/images', 0700);

    $imgSrc = $img->getAttribute('src');

    $newImageName = fileRenamer($imgSrc, $nameDir);

    $dirCatalog = STRUCT_DIR . '/' . $nameDir;

    $imageDirPath = STRUCT_DIR . '/' . $nameDir . '/images';

    if(is_dir($imageDirPath)) {

        file_put_contents($imageDirPath .'/'. $newImageName, file_get_contents(URL_SMARTA . $imgSrc));

    } else {

        $driver->quit();

        die('Dir is not found');

    }

    foreach ($blockLinks as $blockLink) {

        try {
    
            $link = $blockLink->findElement(WebDriverBy::tagName('a'));
            
        } catch (NoSuchElementException $e) {
        
            $driver->quit();
    
            die("No such element link");
    
        }
        
        $textLink = $link->getText();
        $hrefLink = $link->getAttribute('href');
        // Проверяю ссылку если она не ведет на каталог PDF
        if (strpos($textLink, "PDF") !== false) {
    
            continue;
    
        } else {
    
            $relationName = removeQuotes(replaceSpace($textLink));

            file_put_contents($dirCatalog . '/relation.txt', $relationName . "\n", FILE_APPEND);
            file_put_contents($dirCatalog . '/links.txt', URL_SMARTA . $hrefLink . "\n", FILE_APPEND);
    
        }
    
    }
    
}

$driver->quit();
