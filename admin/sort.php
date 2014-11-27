<?php

include dirname(__DIR__) . '/main.php';

$chan->checkSourceUrl();

$tableField = @$_POST['tableField'];
$idSerial   = @$_POST['idSerial'];
$sortSerial = @$_POST['sortSerial'];

if ('' === $idSerial && '' === $sortSerial) {
    exit;
}

$ids   = explode(',', $idSerial);
$sorts = explode(',', $sortSerial);

if (count($ids) != count($sorts)) {
    die('參數錯誤');
}

// start update sort
$chan->table = $tableField;

foreach ($ids as $index => $id) {
    $chan->pk = 'id';
    $chan->pkValue = $id;
    $chan->addField('sort', $sorts[$index], 'int');
    $chan->save();
}
