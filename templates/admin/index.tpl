<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$smarty.const.SITE_TITLE} 管理系統</title>
    <link rel="stylesheet" href="css/initial.css" />
    <link rel="stylesheet" href="css/smoothness/jquery-ui.css" />
    <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css" />
    <link rel="stylesheet" href="css/bootstrap/bootstrap-theme.min.css" />
    <script src="js/jquery/jquery.js" type="text/javascript"></script>
    <script src="js/jquery/jquery-ui.js" type="text/javascript"></script>
    <script src="js/jquery/jquery.prettyPhoto.js" type="text/javascript"></script>
    <script src="js/jquery/jquery.validate.js" type="text/javascript"></script>
    <script src="js/jquery/messages_zh_TW.js" type="text/javascript"></script>
    <script src="js/jquery/jquery.form.js" type="text/javascript"></script>
    <script src="js/bootstrap/bootstrap.min.js" type="text/javascript"></script>
    <script src="js/initial.js" type="text/javascript"></script>
    <script src="js/index.js" type="text/javascript"></script>
</head>
<body>
    {include file="admin/modal.tpl"}
    <div class="navbar navbar-inverse" role="navigation">
        <div class="navbar-inner">
            <div class="container">
                <a class="navbar-brand">{$smarty.const.SITE_TITLE}</a>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="index-center">
                <div class="jumbotron">
                    <h1>{$smarty.const.SITE_TITLE} 管理系統</h1>
                    <p>歡迎使用 {$smarty.const.SITE_TITLE} 後台管理系統</p>
                    <form name="login-form" id="login-form" class="form-line" autocomplete="off">
                        <div class="form-group">
                            <input type="text" id="username" name="username" placeholder="請輸入帳號" class="input-sm form-control">
                        </div>
                        <div class="form-group">
                            <input type="password" id="password" name="password" placeholder="請輸入密碼" class="input-sm form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">登入</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
