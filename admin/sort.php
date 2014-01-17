<?php
include_once '../main.php';
$chan->checkSourceUrl();
$chan->connect();

$tableField = @$_POST['tableField'];
$idSerial   = @$_POST['idSerial'];
$sortSerial = @$_POST['sortSerial'];

if ('' === $idSerial && '' === $sortSerial) {
    exit;
}

$idArr   = explode(',', $idSerial);
$sortArr = explode(',', $sortSerial);

if (count($idArr) != count($sortArr)) {
    exit;
}

// start update sort
$chan->table = $tableField;
foreach ($idArr as $k => $id) {
    $chan->pk = 'id';
    $chan->pkValue = $id;
    $chan->addField('sort', $sortArr[$k], 'int');
    $chan->dataUpdate();
}
