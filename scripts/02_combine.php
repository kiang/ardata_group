<?php
$result = array();
$json1 = json_decode(file_get_contents(dirname(__DIR__) . '/openGroups/groupName_2016.json'));
$json2 = json_decode(file_get_contents(dirname(__DIR__) . '/report/01_extract.json'));

foreach($json1 AS $group) {
    $result[$group->group_no] = array(
        'code' => $group->group_no,
        'name' => $group->name,
        'name_list' => $group->name_list,
        'expenditures' => 0,
        'incomes' => 0,
    );
}

foreach($json2 AS $line) {
    if(isset($result[$line->group])) {
        $result[$line->group]['expenditures'] += $line->expenditures;
        $result[$line->group]['incomes'] += $line->incomes;
    }
}

function cmp($a, $b)
{
    if ($a['incomes'] == $b['incomes']) {
        return 0;
    }
    return ($a['incomes'] > $b['incomes']) ? -1 : 1;
}
usort($result, "cmp");

$fh = fopen(dirname(__DIR__) . '/report/02_combine.csv', 'w');
fputcsv($fh, array('集團', '捐款', '花費', '集團代碼', '相關公司'));
foreach($result AS $line) {
    if($line['incomes'] > 0 || $line['expenditures'] > 0) {
        if(!isset($line['code'])) {
            print_r($line);
            exit();
        }
        $nameList = '';
        if(isset($line['name_list'])) {
            $nameList = implode('/', $line['name_list']);
        }
        fputcsv($fh, array($line['name'], $line['incomes'], $line['expenditures'], $line['code'], $nameList));
    }
}