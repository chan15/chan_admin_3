<?php
include '../main.php';

if (true === isset($_POST['login'])) {
    $chan->checkSourceUrl();
    $username = @$_POST['username'];
    $password = @$_POST['password'];
    $status = '';
    $message = '';

    if ('' !== $username && '' !== $password) {
        $sql = sprintf("SELECT * FROM `admins` WHERE `username` = %s AND `password` = %s",
            $chan->toSql($username, 'text'),
            $chan->toSql($password, 'text'));
        $row = $chan->myOneRow($sql);

        if (null === $row) {
            $status = 'fail';
            $message = '查無資料';
        } else {
            $status = 'ok';
            $_SESSION['admin'] = true;
            $_SESSION['adminId'] = $row['id'];
        }
    }

    echo json_encode(array('status' => $status, 'message' => $message));
    exit;
}

$smarty->display('admin/index.tpl');
?>
