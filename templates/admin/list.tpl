{extends file="admin/layout.tpl"}
{block name="javascript"}
    <script src="js/list.js" type="text/javascript"></script>
{/block}
{block name="content"}
<input type="hidden" name="id-serial" id="id-serial">
<input type="hidden" name="sort-serial" id="sort-serial">
<input type="hidden" name="table-field" id="table-field" value="{$tableField}">
<input type="hidden" name="id-field" id="id-field" value="{$idField}">
<input type="hidden" name="sort-field" id="sort-field" value="{$sortField|default:""}">
<input type="hidden" name="on-filed" id="on-field" value="{$onField|default:''}">
<div class="container">
    <div class="row">
        {if isset($sideBar) && $sideBar != ""}
        <div class="span2">
            <div class="well sidebar-nav">
                <ul class="nav nav-list">
                    {$sideBar}
                </ul>
            </div>
        </div>
        {/if}
        <div class="{$containerSize}">
            <ul class="breadcrumb">
                <li>產品 <span class="divider">/</span></li>
                <li class="active">列表</li>
            </ul>
            <div>
                <form name="search-form" id="search-form" action="{$smarty.server.PHP_SELF}" method="GET" class="form-search">
                    <input type="text" name="name" value="{$smarty.get.name|default:''}">
                    <input type="hidden" name="limit" value={$limit}>
                    <button type="submit" class="btn">搜尋</button>
                    <button type="button" id="btn-reset-search" class="btn" url="{$smarty.server.PHP_SELF}">重置搜尋</button>
                </form>
            </div>
            <div class="func-main">
                <a href="{$modifyPage}" class="btn btn-add">新增</a>
            </div>
            {if $list}
            <div class="func-zone">
                <strong class="data-count">筆數：{$total} 頁數： {$pageNow} / {$pageTotal}</strong>
                <div class="func-button">
                    <button class="btn btn-primary btn-del-all">刪除</button>
                    <form name="limit-form" id="limit-form" action="{$smarty.server.PHP_SELF}" method="GET">
                    {html_options name="limit" id="limit" options=$pageLimitOpt selected=$limit class="span1 limit-number"}
                </form>
                </div>
            </div>
            <table id="main-table" class="table table-striped">
                <thead>
                    <tr>
                        <th><input class="check-all" type="checkbox"></th>
                        <th>名稱</th>
                        <th>啟用</th>
                        <th>功能</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $list as $row}
                    <tr id="{$row.id}" sort="{$row.{$sortField}|default:''}">
                        <td><input type="checkbox" class="checkItem" id="{$row.id}"></td>
                        <td>{$row.t_name}</td>
                        <td>{$yesNoListOpt[$row.t_on]|default:''}</td>
                        <td>
                            <a href="{$modifyPage}?id={$row.id}"><i class="icon-edit"></i></a>
                            <a href="#" id="{$row.id}" name="btn-del" title="刪除"><i class="icon-trash"></i></a>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
            <div>
                <button class="btn btn-primary btn-del-all">刪除</button>
                <button class="btn btn-success" id="btn-save-sort">儲存排序結果</button>
            </div>
            <div class="pagination">
                {$bootstrapPager}
            </div>
            {else}
            {$smarty.const.NO_DATA}
            {/if}
        </div>
    </div>
</div>
{/block}
