<?php
$funcNum = $_GET['CKEditorFuncNum'];
$CKEditor = $_GET['CKEditor'];
$langCode = $_GET['langCode'];
$message = '';
$url = '';
$newName = date('YmdHis'); // 新檔案名稱
$dir = 'uploads/editor/'; // 預設目錄
$ratio = 1200; // 定義寬度

include_once '../libs/class.upload.php'; // 使用 upload class
$foo = new Upload($_FILES['upload']);
if ($foo->uploaded) {
    $foo->file_new_name_body = $newName;
    $foo->image_resize = true;
    $foo->convert = 'jpg'; // 轉換為 jpg
    $foo->jpeg_quality = 100;
    $foo->image_x = $ratio;
    $foo->image_y = $ratio;
	$foo->image_ratio_no_zoom_in = true;
    $foo->process('../'.$dir);
 
    if ($foo->processed) {
        $url = '../'.$dir.$newName.'.jpg';
    } else {
        $message = $foo->error;
    }
} 
 
echo "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
?>
