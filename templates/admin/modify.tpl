{extends file="admin/layout.tpl"}
{block name="javascript"}
<script src="js/modify.js" type="text/javascript"></script>
{/block}
{block name="content"}
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <ul class="breadcrumb">
                <li>產品</li>
                <li class="active">編輯</li>
            </ul>
            <ul class="nav nav-tabs" id="tab-zone">
                <li class="active"><a href="#sec1">基本設定</a></li>
                {* <li><a href="#sec2">name2</a></li> *}
            </ul>
            <form name="modifyForm" id="modifyForm" action="{$smarty.server.PHP_SELF}" method="post" class="form form-horizontal" role="form">
                <div class="tab-content">
                    <div class="tab-pane active" id="sec1">
                        <div class="form-group">
                            <div class="col-md-3">
                                <label>名稱</label>
                                <input type="text" name="name" value="{$data.name|default:''}" class="form-control isNeed">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3">
                                <label>產品圖片</label>
                                <div>
                                    {if '' !== $data.image}
                                    {$data.image|thumb:$path:100:100:''}
                                    {/if}
                                </div>
                                <input id="image" name="image" type="file">
                                <span class="help-block">建議尺寸 900 x 900</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-3">
                                <label>上架</label>
                                <div class="control-group">
                                    {html_radios name="on" options=$yesNoOption selected={$data.on|default:''} class="isNeed"}
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-8">
                                <label>商品訊息</label>
                                <textarea name="content" rows="6" class="form-control ckeditor">{$data.content|default:''}</textarea>
                            </div>
                        </div>
                    </div>
                    {* <div class="tab-pane" id="sec2"> *}
                    {*     <p>second</p> *}
                    {* </div> *}
                    <div class="control-group">
                        {if isset($smarty.get.id)}
                            <button type="submit" name="btn-update" id="btn-update" class="btn btn-primary">更新</button>
                            <input name="id" type="hidden" value="{$smarty.get.id}">
                        {else}
                            <button type="submit" name="btn-add" id="btn-add" class="btn btn-primary">儲存</button>
                        {/if}
                        <input name="modify" type="hidden" value="true">
                        <input type="hidden" name="back-page" id="back-page" value="{$smarty.server.HTTP_REFERER|default:'admin.php'}">
                        <button type="reset" class="btn">重設</button>
                        <button type="button" class="btn btn-back">回上頁</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
{/block}