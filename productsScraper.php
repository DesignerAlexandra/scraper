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
            
            if(in_array($subgroup, $exceptionsDir)) {
                continue;
            }

            if(is_dir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $subgroup)) {

                $subgroups = scandir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $subgroup);

            } else {

               var_dump($subgroup);

            }

            foreach ($subgroups as $subgroupForSubgroup) {
                
                $path = '';
                $pathDir = '';

                if(in_array($subgroupForSubgroup, $exceptionsDir)) {
                    continue;
                }

                if(is_dir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $subgroup . '/' . $subgroupForSubgroup)) {

                    $nextSungroups = scandir(STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $subgroup . '/' . $subgroupForSubgroup);

                } else {

                    $path = STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $subgroup . '/' . $subgroupForSubgroup;
                    $pathDir = STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $subgroup;
                    scraperProducts($serverUrl, $path, $pathDir, $subgroupForSubgroup);

                }
                
                foreach ($nextSungroups as $nextSungroup) {
                     
                    if(in_array($nextSungroup, $exceptionsDir)) {
                        continue;
                    }

                    $path = STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $subgroup . '/' . $subgroupForSubgroup . '/' . $nextSungroup;
                    $pathDir = STRUCT_DIR . '/' . $dir . '/' . $group . '/' . $subgroup . '/' . $subgroupForSubgroup;
                    scraperProducts($serverUrl, $path, $pathDir, $subgroupForSubgroup);

                }
            }
        }
    }
}

function scraperProducts($url, $path, $pathForDir , $fileName)
{

    $checkHeader = [];

    $driver = RemoteWebDriver::create($url, DesiredCapabilities::firefox());

    $stream = fopen($path, 'r');
    $newFile = str_replace('.txt', '', $fileName);
    $newFileCSV = $newFile . '.csv';
    $stream2 = fopen($pathForDir . '/' . $newFileCSV, 'w');

    while ($row = fgets($stream)) {
        var_dump(trim($row));
        $driver->navigate()->to(trim($row));
        try {
            $table = $driver->findElement(WebDriverBy::tagName('tbody'));
            $tr = $table->findElements(WebDriverBy::cssSelector('tr'));

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

        $data[] = $newPrice;
        $data[] = $stock;
        fputcsv($stream2, $data, ';');


        // writer(RemoteWebDriver::create($serverUrl, DesiredCapabilities::firefox()), trim($row));
    }

    $driver->quit();

}