<?php
$openGroups = array();
$groupFh = array();
/**
 * openGroups files from https://github.com/starsdog/openGroups
 */
foreach(glob(dirname(__DIR__) . '/openGroups/*.csv') AS $csvFile) {
    $p = pathinfo($csvFile);
    $parts = explode('_', $p['basename']);
    $fh = fopen($csvFile, 'r');
    fgetcsv($fh, 2048);
    $fhCreated = false;
    while($line = fgetcsv($fh, 2048)) {
        $line[12] = $parts[0];
        if(false === $fhCreated) {
            $fhCreated = true;
            $rawPath = dirname(__DIR__) . '/raw/2018/' . $line[12];
            if(!file_exists($rawPath)) {
                mkdir($rawPath, 0777, true);
            }
            $groupFh[$line[12]] = array(
                'expenditures' => $rawPath . '/expenditures.csv',
                'incomes' => $rawPath . '/incomes.csv',
            );
        }
        if($line[18] == '1') {
            continue;
        }
        if(!isset($openGroups[$line[13]]) && strlen($line[13]) === 8) {
            $openGroups[$line[13]] = array(
                'group' => $line[12],
                'expenditures' => 0,
                'incomes' => 0,
            );
        }
        if(!isset($openGroups[$line[14]]) && strlen($line[14]) === 8) {
            $openGroups[$line[14]] = array(
                'group' => $line[12],
                'expenditures' => 0,
                'incomes' => 0,
            );
        }
    }
}

$zip = new ZipArchive;
$expenditures = $incomes = array();
foreach(glob('/home/kiang/public_html/ardata.cy.gov.tw/data/individual/account/107年*/*/*.zip') AS $zipFile) {
    $fh = fopen("zip://{$zipFile}#expenditures.csv", 'r');
    fgetcsv($fh, 2048);
    while($line = fgetcsv($fh, 2048)) {
        if(!isset($line[7])) {
            continue;
        }
        $line[7] = trim($line[7]);
        if(!empty($line[7]) && false === strpos($line[7], '*') && isset($openGroups[$line[7]])) {
            $oFh = fopen($groupFh[$openGroups[$line[7]]['group']]['expenditures'], 'a+');
            fputcsv($oFh, $line);
            fclose($oFh);
            $openGroups[$line[7]]['expenditures'] += intval($line[9]);
        }
    }

    $fh = fopen("zip://{$zipFile}#incomes.csv", 'r');
    fgetcsv($fh, 2048);
    while($line = fgetcsv($fh, 2048)) {
        if(!isset($line[7])) {
            continue;
        }
        $line[7] = trim($line[7]);
        if(!empty($line[7]) && false === strpos($line[7], '*') && isset($openGroups[$line[7]])) {
            $oFh = fopen($groupFh[$openGroups[$line[7]]['group']]['incomes'], 'a+');
            fputcsv($oFh, $line);
            fclose($oFh);
            $openGroups[$line[7]]['incomes'] += intval($line[8]);
        }
    }
}

file_put_contents(dirname(__DIR__) . '/report/01_extract_2018.json', json_encode($openGroups));