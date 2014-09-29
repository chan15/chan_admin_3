<?php

error_reporting(0);
header('Content-type: text/html; charset=utf-8');
include 'const.php';
include 'config.php';
include 'vendor/autoload.php';

$smarty = new Smarty;

$path = dirname(__FILE__);
$smarty->template_dir = $path . '/templates/';
$smarty->compile_dir = $path . '/templates_c/';
$smarty->config_dir = $path . '/configs/';
$smarty->cache_dir = $path . '/cache/';

include 'libs/class.chan.php';
$chan = new Chan;

$chan->host = DB_HOST;
$chan->db = DB_DB;
$chan->username = DB_USERNAME;
$chan->password = DB_PASSWORD;

$chan->sessionOn();
