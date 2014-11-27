<?php

include '../main.php';
include 'login-policy.php';
$chan->checkSourceUrl();

$id = $_POST['id'];
$tableField = $_POST['tableField'];
$ids = explode(',', $id);

foreach ($ids as $id) {
    $chan->table = $tableField;
	$chan->pk = 'id';
	$chan->pkValue = $id;

	// Delete file if needed
	switch($tableField) {
        case 'table':
			$path = '../uploads/test/';
			$chan->fileDeleteArray[] = $chan->getFileName('image');
			$chan->dataFileDelete($path);
            break;
    }

	$chan->delete();

    // Delete detail data if needed
    switch ($tableField) {
        case 'table':
            $sqlDetail = "SELECT `id`, `image` FROM `detail_table` WHERE `fk` = ?";
            $chan->addValue($id, 'int');
            $rowDetail = $chan->myRow($sqlDetail);

            if (null !== $rowDetail) {
                foreach ($rowDetail as $detail) {
                    $chan->table = 'detail_table';
                    $chan->pk = 'id';
                    $chan->pkValue = $detail['id'];
                    $chan->fileDeleteArray[] = $detail['image'];
                    $chan->dataFileDelete($path);
                    $chan->delete();
                }
            }

            break;
    }
}
