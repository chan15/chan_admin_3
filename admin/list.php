<?php
include '../main.php';
$loginAuth = 0;
include 'login-policy.php';
$pageItemName = ''; // nav name
$subItemName = ''; // sidebar name
include 'nav.php';
include 'options.php';
$smarty->assign('pageLimitOpt', $pageLimitOpt);
$smarty->assign('yesNoOpt', $yesNoOpt);
$limit = isset($_GET['limit']) ? $_GET['limit'] : 20;
$smarty->assign('limit', $limit);
$chan->dbConnect();

$name = isset($_GET['name']) ? '%'.$_GET['name'].'%' : '%%%';
$on = (isset($_GET['on']) && $_GET['on'] != '') ? '%'.$_GET['on'].'%' : '%%%';

$sql = sprintf("SELECT t_id AS id, tbl_test.*
    FROM tbl_test 
    WHERE t_name LIKE %s 
    ORDER BY t_id DESC",
    $chan->toSql($name, 'text'));
$row = $chan->myRowList($sql, $limit);

$smarty->assign('tableField', 'tbl_test'); // table field
$smarty->assign('idField', 't_id'); // id field
$smarty->assign('onField', 't_on'); // sort field
$smarty->assign('sortField', ''); // sort field
$smarty->assign('modifyPage', 'test-modify.php'); // modify page
$smarty->assign('total', $chan->totalRecordCount); // total record
$smarty->assign('pageNow', ($chan->page+1)); // current page
$smarty->assign('pageTotal', ($chan->totalPages+1)); // total page
$smarty->assign('bootstrapPager', $chan->bootstrapPager()); // pager
$smarty->assign('list', $row);

$smarty->display('admin/test-list.tpl');
?>
