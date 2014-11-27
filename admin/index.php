<?php

include dirname(__DIR__) . '/main.php';

if (true === isset($_POST['login'])) {
    $chan->checkSourceUrl();
    $username = @$_POST['username'];
    $password = @$_POST['password'];
    $status = '';
    $message = '';

    if ('' !== $username && '' !== $password) {
        $sql = 'SELECT * FROM `admins` WHERE `username` = ? AND `password` = ?';
            $chan->addValue($username);
            $chan->addValue($password);
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
