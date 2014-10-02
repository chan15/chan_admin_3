<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$smarty.const.ADMIN_TITLE} 管理系統</title>
	<link rel="shortcut icon" href="images/admin.ico" type="image/x-icon" />
    <link rel="stylesheet" href="css/smoothness/jquery-ui.css" />
    <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css" />
    <link rel="stylesheet" href="css/bootstrap/bootstrap-theme.min.css" />
    <link rel="stylesheet" href="css/prettyPhoto.css" />
    <link rel="stylesheet" href="css/initial.css" />
    {block name="css"}{/block}
    <script src="js/jquery/jquery.js" type="text/javascript"></script>
    <script src="js/jquery/jquery-ui.js" type="text/javascript"></script>
    <script src="js/jquery/jquery.prettyPhoto.js" type="text/javascript"></script>
    <script src="js/jquery/jquery.validate.js" type="text/javascript"></script>
    <script src="js/jquery/messages_zh_TW.js" type="text/javascript"></script>
    <script src="js/jquery/jquery.form.js" type="text/javascript"></script>
    <script src="js/jquery/jquery.relatedselects.min.js" type="text/javascript"></script>
    <script src="js/bootstrap/bootstrap.min.js" type="text/javascript"></script>
    <script src="ckeditor/ckeditor.js" type="text/javascript"></script>
    <script src="ckeditor/adapters/jquery.js" type="text/javascript"></script>
    <script src="js/initial.js" type="text/javascript"></script>
    {block name="javascript"}{/block}
</head>
<body>
    {include file="admin/modal.tpl"}
    <div class="navbar navbar-inverse" role="navigation">
        <div class="navbar-inner">
            <div class="container">
                <a class="navbar-brand">{$smarty.const.ADMIN_TITLE}</a>
                <ul class="nav navbar-nav">
                    {$nav}
                </ul>
            </div>
        </div>
    </div>
    {block name="content"}{/block}
</body>
</html>
