<?php
include '../main.php';
include 'login-policy.php';
$chan->checkSourceUrl();
$chan->connect();

$id = $_POST['id'];
$tableField = $_POST['tableField'];
$idArr = explode(',', $id);

foreach ($idArr as $k) {
    $chan->table = $tableField;
	$chan->pk = 'id';
	$chan->pkValue = $k;
	
	// Delete file if needed
	switch($tableField) {
        case 'table':
			$path = '../uploads/test/';
			$chan->fileDeleteArray[] = $chan->getFileName('image');
			$chan->dataFileDelete($path);
        break;
    }
	
	$chan->dataDelete();

    // Delete detail data if needed
    switch ($tableField) {
        case 'table':
            $sqlDetail = sprintf("SELECT `id`, `image` FROM `detail_table` WHERE `fk` = %s",
                $chan->toSql($k, 'int'));
            $rowDetail = $chan->myRow($sqlDetail);
            if ($rowDetail) {
                foreach ($rowDetail as $detail) {
                    $chan->table = 'detail_table';
                    $chan->pk = 'id';
                    $chan->pkValue = $detail['id'];
                    $chan->fileDeleteArray[] = $detail['image'];
                    $chan->dataFileDelete($path);
                    $chan->dataDelete();
                }
            }
        break;
    }
}
?>
