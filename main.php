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

$chan = new Chan\Chan('single');
