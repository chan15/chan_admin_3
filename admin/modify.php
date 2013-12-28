<?php
include '../main.php';
$loginAuth = 0;
include 'login-policy.php';
$pageItemName = '';
$subItemName = '';
$tableName = '';
$fileName = '';
include 'nav.php';
include 'options.php';
$smarty->assign('yesNoOption', $yesNoOption);
$path = '../uploads/' . $tableName . '/';
$smarty->assign('path', $path);
$fileField = array();
$fileRealField = array();
$haveUpload = false;
$isUpdate = (isset($_POST['id'])) ? true : false;
$chan->imageUploadRatio = 600;
$chan->connect();

// Ajax modify
if (isset($_POST['modify'])) {
	$chan->checkSourceUrl();
	$chan->table = $tableName; 
	$chan->addValidateField('名稱', 'name');
	$chan->addValidateField('上架', 'on');

    if (false === $isUpdate) {
        $chan->addValidateField('圖片', 'image', 'file');
    }

	$chan->serverValidate();

	if ($chan->validateError) {
		echo $chan->validateMessage;
	} else {
		if (true === $haveUpload) {
			foreach ($fileField as $k => $field) {
				if ('' !== $_FILES[$field]['name']) {
					$upload = $chan->imageUpload($path, $field);

					if ('' !== $upload['err']) {
						echo $upload['err'];
						exit;
					} else {
						$chan->addField($fileRealField[$k], $upload['img']);
					}
				}
			}
		}

		$chan->addField('name', $_POST['name']);
		$chan->addField('on', $_POST['on']);
        $chan->addField('admin_id', $_SESSION['adminId'], 'int');


        if (false === $isUpdate) {
            $chan->addField('sort', $chan->retMaxSort('sort'), 'int');
            $chan->addField('created_at', $chan->retNow(), 'date');

            if (!$chan->dataInsert()) {
                echo $chan->sqlError;
            }
        } else {
            $chan->pk = 'id';
            $chan->pkValue = $_POST['id'];

            if (!$chan->dataUpdate()) {
                echo $chan->sqlError;
            }
        }

	}

	exit;
}

// load data
if (isset($_GET['id'])) {
	$sql = sprintf("SELECT * FROM `%s` WHERE `id` = %s",
		$tableName,
		$chan->toSql($_GET['id'], 'int'));
	$row = $chan->myOneRow($sql);
	$smarty->assign('data', $row);
}

$smarty->display('admin/' . $fileName . '-modify.tpl');
