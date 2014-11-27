<?php

include dirname(__DIR__) . '/main.php';

$loginAuth = 0;
include 'login-policy.php';
$pageItemName = '';
$subItemName = '';
$tableName = '';
$fileName = '';
include 'nav.php';
include 'options.php';
$options = include 'options.php';
$smarty->assign('options', $options);
$limit = (true === isset($_GET['limit'])) ? $_GET['limit'] : 20;
$smarty->assign('limit', $limit);

$name = (true === isset($_GET['name']) && '' !== $_GET['name']) ? '%' . $_GET['name'] . '%' : '%%%';
$on = (true === isset($_GET['on']) && '' !== $_GET['on']) ? '%' . $_GET['on'] . '%' : '%%%';

$sql = sprintf("SELECT *
    FROM `%s`
    WHERE `name` LIKE ? AND `on` LIKE ?
    ORDER BY `id` DESC",
    $tableName);
$chan->addValue($name);
$chan->addValue($on);
$row = $chan->myRowList($sql, $limit);

$smarty->assign('tableField', $tableName); // table field
$smarty->assign('total', $chan->totalRecordCount); // total record
$smarty->assign('pageNow', ($chan->page + 1)); // current page
$smarty->assign('pageTotal', ($chan->totalPages + 1)); // total page
$smarty->assign('bootstrapPager', $chan->bootstrapPager()); // pager
$smarty->assign('datas', $row);
$smarty->assign('modifyPage', $fileName . '-modify.php');
$smarty->display('admin/' . $fileName . '-list.tpl');
