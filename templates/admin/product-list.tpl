{extends file="admin/layout.tpl"}
{block name="javascript"}
    <script src="js/list.js" type="text/javascript"></script>
{/block}
{block name="content"}
<input type="hidden" name="id-serial" id="id-serial">
<input type="hidden" name="sort-serial" id="sort-serial">
<input type="hidden" name="table-field" id="table-field" value="{$tableField}">
<input type="hidden" name="sort-field" id="sort-field" value="{$hasSort|default:"no"}">
<div class="container">
    <div class="row">
        <ol class="breadcrumb">
            <li>產品</li>
            <li class="active">列表</li>
        </ol>
    </div>
    <div class="row">
        <form name="search-form" id="search-form" action="{$smarty.server.PHP_SELF}" method="GET" class="form-inline" role="form">
            <div class="form-group">
                <input type="text" name="name" value="{$smarty.get.name|default:''}" class="form-control input-sm" placeholder="請輸入名稱">
            </div>
            <div class="form-group">
                {html_options name="online" options=$yesNoSearchOpt selected="{$smarty.get.online|default:''}" class="form-control input-sm"}
            </div>
            <input type="hidden" name="limit" value={$limit}>
            <button type="submit" class="btn btn-sm">搜尋</button>
            <button type="button" id="btn-reset-search" class="btn btn-sm" url="{$smarty.server.PHP_SELF}">重置搜尋</button>
        </form>
    </div>
    <div class="row text-right">
        <a href="{$modifyPage}" class="btn btn-sm btn-default btn-add">新增</a>
    </div>
    {if $list}
    <div class="row text-right">
        <form name="limit-form" id="limit-form" action="{$smarty.server.PHP_SELF}" method="GET" class="form-inline" role="form">
            <strong class="data-count">筆數：{$total} 頁數： {$pageNow} / {$pageTotal}</strong>
            <div class="form-group">
                {html_options name="limit" id="limit" options=$pageLimitOpt selected=$limit class="input-sm form-control"}
            </div>
        </form>
    </div>
    <div class="row">
        <button class="btn btn-sm btn-primary btn-del-all">刪除</button>
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
            <tr id="{$row.id}" sort="{$row.sort|default:''}">
                <td><input type="checkbox" class="check-item" id="{$row.id}"></td>
                <td>{$row.name}</td>
                <td>{$yesNoListOpt[$row.online]|default:''}</td>
                <td>
                    <a href="{$modifyPage}?id={$row.id}" class="btn btn-default btn-xs" title="編輯">編輯</a>
                    <a href="#" id="{$row.id}" name="btn-del" class="btn btn-danger btn-xs" title="刪除">刪除</a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    <div class="row">
        <button class="btn btn-sm btn-primary btn-del-all">刪除</button>
        <button class="btn btn-sm btn-success" id="btn-save-sort">儲存排序結果</button>
    </div>
    <div class="pagination-container">
        {$bootstrapPager}
    </div>
    {else}
        {$smarty.const.NO_DATA}
    {/if}
</div>
{/block}
