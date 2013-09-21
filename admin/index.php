<?php
include '../main.php';

if (isset($_POST['login'])) {
    $chan->checkSourceUrl();
    $username = @$_POST['username'];
    $password = @$_POST['password'];
    $status = '';
    $message = '';

    if ($username !='' && $password != '') {
        $chan->dbConnect();
        $sql = sprintf("SELECT * FROM `admin` WHERE `username` = %s AND `password` = %s",
            $chan->toSql($username, 'text'),
            $chan->toSql($password, 'text'));
        $row = $chan->myOneRow($sql);

        if (!$row) {
            $status = 'fail';
            $message = '查無資料';
        } else {
            $status = 'ok';
            $_SESSION['admin'] = true;
        }
    }

    echo json_encode(array('status' => $status, 'message' => $message));
    exit;
}

$smarty->display('admin/index.tpl');
?>
