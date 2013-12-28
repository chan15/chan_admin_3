<?php
include '../main.php';
$chan->checkSourceUrl();

$chan->connect();
$chan->table = @$_POST['tableField'];
$chan->pk = 'id';
$chan->pkValue= @$_POST['id'];
$chan->addField('on', @$_POST['action'], 'int');
$chan->dataUpdate();
