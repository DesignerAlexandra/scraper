<?php

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/constants.php';



function runnerInDir($path = STRUCT_DIR, )
{
    $exceptionsDir = ['.', '..', 'images', 'links.txt', 'relation.txt'];
    $serverUrl = 'http://localhost:4444';


    if(!is_dir($path)) {
        die('Dont is dirrectory');
    }

    $dirs = scandir($path);

    foreach ($dirs as $file) {
        
        if(in_array($file, $exceptionsDir) || strpos($file, '.csv')) {
            continue;
        }

        $prefix = "/$file";
        $newPath = $path . $prefix;

        if(!is_dir($newPath)) {

            scraperProducts($serverUrl, $newPath, $path, $file);

        } else {

            
            runnerInDir($newPath);

        }
    }
}

runnerInDir();


function scraperProducts($url, $path, $pathForDir , $fileName)
{

    $checkHeader = [];

    $driver = RemoteWebDriver::create($url, DesiredCapabilities::firefox());

    $stremUrlWrite = fopen(__DIR__ . '/doneUrl.txt', 'a');

    $stream = fopen($path, 'r');
    $newFile = str_replace('.txt', '', $fileName);
    $newFileCSV = $newFile . '.csv';
    $stream2 = fopen($pathForDir . '/' . $newFileCSV, 'a');
    $doneUrls = file(__DIR__ . '/doneUrl.txt');

    $urls = [];
    foreach ($doneUrls as $value) {
        $urls[] = trim($value);
    }

    while ($row = fgets($stream)) {

        if(in_array(trim($row), $urls)) continue;

        var_dump(trim($row));
        $driver->get(trim($row));
        try {
            $table = $driver->findElement(WebDriverBy::tagName('table'));
            $tr = $table->findElements(WebDriverBy::tagName('tr'));

        } catch (NoSuchElementException $th) {
            $driver->quit();
            continue;
            var_dump('элемент не найден');
        }


        $header = ['headers'];
        $data = [];

        try {
            $price = $driver->findElement(WebDriverBy::cssSelector('span.product-detail__price-new'))->getText();
            $stockBlock = $driver->findElement(WebDriverBy::className('product-detail__stock'));
            $stock = $stockBlock->findElement(WebDriverBy::tagName('span'))->getText();
            $h1 = $driver->findElement(WebDriverBy::className('product-detail__title'));
            $title = $h1->getText();
        } catch (NoSuchElementException $th) {
            var_dump('элемент не найден');
            $driver->quit();
            continue;
        }
        

        foreach ($tr as $key => $el) {
            try {
                $td = $el->findElements(WebDriverBy::cssSelector('td.td_attr_table_border'));
            } catch (NoSuchElementException $th) {
                var_dump('элемент не найден');
            }

            $header[] = $td[0]->getText();
            $data[] = $td[1]->getText();
        }

        $header[] = 'name';
        $header[] = 'price';
        $header[] = 'stock';
        if(count($checkHeader) != count($header)) {
            $flag = true;
        }

        if($flag) {
            $checkHeader = $header;
            fputcsv($stream2, $header, ';');
            $flag = false;
        }

        $newPrice = preg_replace("/[^0-9.]/", "", $price);
        if (substr($newPrice, -1) == ".") {
            $newPrice = substr($newPrice, 0, -1);
        }

        $data[] = $title;
        $data[] = $newPrice;
        $data[] = $stock;
        fputcsv($stream2, $data, ';');
        fputs($stremUrlWrite, trim($row) . "\n");

        // writer(RemoteWebDriver::create($serverUrl, DesiredCapabilities::firefox()), trim($row));
    }

    $driver->quit();

}