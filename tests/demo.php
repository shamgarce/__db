<?php
include("../vendor/autoload.php");



$wise = Sham\Wise\Wise::getInstance();
$wise->load('mysql','./config.php');

$ms = $wise('mysql');

/**
 * 获取数据库对象
 */
$db = new Sham\Db\Db($ms);

//
//$db->getall();
//







print_r($db);
//
//$config = $wise->C();
//print_r($config);
