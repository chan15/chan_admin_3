<?php
include '../main.php';
$chan->checkSourceUrl();

$chan->dbConnect();
$chan->table = @$_POST['tableField'];
$chan->pk = 'id';
$chan->pkValue= @$_POST['id'];
$chan->addField('online', @$_POST['action'], 'int');
$chan->dataUpdate();
?>

