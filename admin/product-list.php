<?php
include '../main.php';
$loginAuth = 0;
include 'login-policy.php';
$pageItemName = 'product'; // nav name
$table = 'product';
include 'nav.php';
$templateName = 'admin/product-list.tpl';
$modifyPageName = 'product-modify.php';
include 'options.php';
$smarty->assign('pageLimitOpt', $pageLimitOpt);
$smarty->assign('yesNoListOpt', $yesNoListOpt);
$smarty->assign('yesNoSearchOpt', $yesNoSearchOpt);
$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
$smarty->assign('limit', $limit);
$chan->dbConnect();

$name = isset($_GET['name']) ? '%'.$_GET['name'].'%' : '%%%';
$online = (isset($_GET['online']) && $_GET['online'] != '') ? '%'.$_GET['online'].'%' : '%%%';

$sql = sprintf("SELECT *
    FROM `%s`
    WHERE `name` LIKE %s AND `online` LIKE %s
    ORDER BY `id` DESC",
    $table,
    $chan->toSql($name, 'text'),
    $chan->toSql($online, 'text'));
$row = $chan->myRowList($sql, $limit);

$smarty->assign('tableField', $table); // table field
$smarty->assign('hasSort', 'yes');
$smarty->assign('modifyPage', $modifyPageName); // modify page
$smarty->assign('total', $chan->totalRecordCount); // total record
$smarty->assign('pageNow', ($chan->page+1)); // current page
$smarty->assign('pageTotal', ($chan->totalPages+1)); // total page
$smarty->assign('bootstrapPager', $chan->bootstrapPager()); // pager
$smarty->assign('list', $row);

$smarty->display($templateName);
?>
