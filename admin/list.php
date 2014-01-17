<?php
include '../main.php';
$loginAuth = 0;
include 'login-policy.php';
$pageItemName = '';
$subItemName = '';
$tableName = '';
$fileName = '';
include 'nav.php';
include 'options.php';
$smarty->assign('yesNoSearchOption', $yesNoSearchOption);
$smarty->assign('yesNoListOption', $yesNoListOption);
$smarty->assign('pageLimitOption', $pageLimitOption);
$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
$smarty->assign('limit', $limit);
$chan->connect();

$name = (isset($_GET['name']) && '' !== $_GET['name']) ? '%' . $_GET['name'] . '%' : '%%%';
$on = (isset($_GET['on']) && '' !== $_GET['on']) ? '%' . $_GET['on'] . '%' : '%%%';

$sql = sprintf("SELECT *
    FROM `%s` 
    WHERE `name` LIKE %s AND `on` LIKE %s
    ORDER BY `id` DESC",
    $tableName,
    $chan->toSql($name, 'text'),
    $chan->toSql($on, 'text'));
$row = $chan->myRowList($sql, $limit);

$smarty->assign('tableField', $tableName); // table field
$smarty->assign('total', $chan->totalRecordCount); // total record
$smarty->assign('pageNow', ($chan->page + 1)); // current page
$smarty->assign('pageTotal', ($chan->totalPages + 1)); // total page
$smarty->assign('bootstrapPager', $chan->bootstrapPager()); // pager
$smarty->assign('datas', $row);
$smarty->assign('modifyPage', $fileName . '-modify.php');
$smarty->display('admin/' . $fileName . '-list.tpl');
