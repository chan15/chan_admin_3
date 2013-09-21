<?php
include '../main.php';
$loginAuth = 0;
include 'login-policy.php';
$pageItemName = ''; // nav name
$subItemName = ''; // sidebar name
include 'nav.php';
include 'options.php';
$smarty->assign('yesNoOpt', $yesNoOpt);

$theTable = 'tbl_test';
$thePk = 't_id';
$path = '../uploads/test/';
$fileField = array('img');
$fileRealField = array('t_img');
$haveUpload = false;
$chan->imageUploadRatio = 600;
$chan->dbConnect();

// ajax add
if (isset($_POST['add'])) {
	$chan->checkSourceUrl();
	$chan->table = $theTable; 
	$chan->addValidateField('名稱', 'name');
	$chan->addValidateField('上架', 'on');
	// $chan->addValidateField('產品編號', 'serial', 'duplicate', 'serial_number');
	$chan->serverValidate();

	if ($chan->validateErroror) {
		echo $chan->validateMessage;
	} else {
		if ($haveUpload) {
			foreach ($fileField as $k => $field) {
				if ($_FILES[$field]['name'] != '') {
					$upload = $chan->imgUpload($path, $field);
					if ($upload['err'] != '') {
						echo $upload['err'];
						exit;
					} else {
						$chan->addField($fileRealField[$k], $upload['img']);
					}
				}
			}
		}

		$chan->addField('t_name', $_POST['name']);
		$chan->addField('t_on', $_POST['on']);
        $chan->addField('t_sort', $chan->retMaxSort('t_sort'), 'int');
        $chan->addField('t_build_time', $chan->retNow(), 'date');
        $chan->addField('admin_id', $_SESSION['adminId'], 'int');

		if (!$chan->dataInsert()) {
			echo $chan->sqlError;
		}
	}

	exit;
}

// ajax update
if (isset($_POST['update'])) {
	$chan->checkSourceUrl();
	$chan->table = $theTable;
	$chan->pk = $thePk;
	$chan->pkValue = $_POST['id'];
	$chan->addValidateField('名稱', 'name');
	$chan->addValidateField('上架', 'on');
	$chan->serverValidate();

	if ($chan->validateErroror) {
		echo $chan->validateMessage;
	} else {
		if ($haveUpload) {
			foreach ($fileField as $k => $field) {
				if ($_FILES[$field]['name'] != '') {
					$upload = $chan->imgUpload($path, $field);
					if ($upload['err'] != '') {
						echo $upload['err'];
						exit;
					} else {
						$chan->addField($fileRealField[$k], $upload['img']);
						$chan->fileDeleteArray[] = $chan->getFileName($fileRealField[$k]);
					}
				}
			}
		}

		$chan->addField('t_name', $_POST['name']);
		$chan->addField('t_on', $_POST['on']);
        $chan->addField('admin_id', $_SESSION['adminId'], 'int');

		if (!$chan->dataUpdate()) {
			echo $chan->sqlError;
		} else {
			$chan->dataFileDelete($path);
		}
	}

	exit;
}

// load data
if (isset($_GET['id'])) {
	$sql = sprintf("SELECT * FROM %s WHERE %s = %s",
		$theTable,
		$thePk,
		$chan->toSql($_GET['id'], 'int'));
	$row = $chan->myOneRow($sql);
	$smarty->assign('data', $row);
}

$smarty->display('admin/test-modify.tpl');
?>
