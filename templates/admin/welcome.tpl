{extends file="admin/layout.tpl"}
{block name="content"}
    <div class="container">
        <div class="row">
            <div class="index-center">
                <div class="jumbotron">
                    <h1>{$smarty.const.ADMIN_TITLE} 管理系統</h1>
                    <p>歡迎使用 {$smarty.const.ADMIN_TITLE} 後台管理系統</p>
                    {html_image file="images/browser/chrome.png"}
                    {html_image file="images/browser/firefox.png"}
                    {html_image file="images/browser/safari.png"}
                    {html_image file="images/browser/opera.png"}
                    {html_image file="images/browser/ie.png"}
                </div>
            </div>
        </div>
    </div>
{/block}
