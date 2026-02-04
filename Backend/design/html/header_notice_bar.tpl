{* Заголовок *}
{$meta_title=$btr->sviat_header_notice_bar_title scope=global}

{*Назва сторінки*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->sviat_header_notice_bar_title|escape}
            </div>
            <div class="box_btn_heading">
                <a class="btn btn_small btn-info" href="{url controller=[Sviat,HeaderNoticeBar,HeaderNoticeBarBannerAdmin] return=$smarty.server.REQUEST_URI}">
                    {include file='svg_icon.tpl' svgId='plus'}
                    <span>{$btr->sviat_header_notice_bar_add|escape}</span>
                </a>
            </div>
        </div>
    </div>
</div>

{*Головна форма сторінки*}
<div class="boxed fn_toggle_wrap">
    {if $banners}
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <form id="list_form" method="post" class="fn_form_list fn_fast_button">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">
                    <div class="okay_list">
                    {*Шапка таблиці*}
                    <div class="okay_list_head">
                        <div class="okay_list_boding okay_list_drag"></div>
                        <div class="okay_list_heading okay_list_check">
                            <input class="hidden_check fn_check_all" type="checkbox" id="check_all_1" name="" value=""/>
                            <label class="okay_ckeckbox" for="check_all_1"></label>
                        </div>
                        <div class="okay_list_heading okay_list_features_name">{$btr->sviat_header_notice_bar_name|escape}</div>
                        <div class="okay_list_heading okay_list_brands_tag">{$btr->sviat_header_notice_bar_publish_dates|escape}</div>
                        <div class="okay_list_heading okay_list_status">{$btr->general_enable|escape}</div>
                        <div class="okay_list_heading okay_list_close"></div>
                    </div>
                    {*Параметри елемента*}
                    <div id="sortable" class="okay_list_body sortable">
                    {foreach $banners as $banner}
                        <div class="fn_row okay_list_body_item">
                            <div class="okay_list_row">
                                <input type="hidden" name="positions[{$banner->id}]" value="{$banner->position|escape}">

                                <div class="okay_list_boding okay_list_drag move_zone">
                                    {include file='svg_icon.tpl' svgId='drag_vertical'}
                                </div>

                                <div class="okay_list_boding okay_list_check">
                                    <input class="hidden_check" type="checkbox" id="id_{$banner->id}" name="check[]" value="{$banner->id}"/>
                                    <label class="okay_ckeckbox" for="id_{$banner->id}"></label>
                                </div>

                                <div class="okay_list_boding okay_list_features_name">
                                    <a href="{url controller=[Sviat,HeaderNoticeBar,HeaderNoticeBarBannerAdmin] id=$banner->id return=$smarty.server.REQUEST_URI}">
                                        {$banner->name|escape|truncate:50}
                                    </a>
                                </div>

                                <div class="okay_list_boding okay_list_brands_tag">
                                    {if $banner->publish_from || $banner->publish_to}
                                        {if $banner->publish_from}
                                            {$banner->publish_from|date_format:"%d.%m.%Y %H:%M"}
                                        {/if}
                                        {if $banner->publish_from && $banner->publish_to} - {/if}
                                        {if $banner->publish_to}
                                            {$banner->publish_to|date_format:"%d.%m.%Y %H:%M"}
                                        {/if}
                                    {else}
                                        {$btr->sviat_header_notice_bar_always|escape}
                                    {/if}
                                </div>

                                <div class="okay_list_boding okay_list_status">
                                    {*visible*}
                                    <label class="switch switch-default ">
                                        <input class="switch-input fn_ajax_action {if $banner->visible}fn_active_class{/if}" data-controller="sviat__header_notice_bar" data-action="visible" data-id="{$banner->id}" name="visible" value="1" type="checkbox"  {if $banner->visible}checked=""{/if}/>
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>

                                <div class="okay_list_boding okay_list_close">
                                    <button data-hint="{$btr->general_delete|escape}" type="button" class="btn_close fn_remove hint-bottom-right-t-info-s-small-mobile  hint-anim" data-toggle="modal" data-target="#fn_action_modal" onclick="success_action($(this));">
                                        {include file='svg_icon.tpl' svgId='trash'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                        </div>

                        {*Блок массовых действий*}
                        <div class="okay_list_footer fn_action_block">
                            <div class="okay_list_foot_left">
                                <div class="okay_list_boding okay_list_drag"></div>
                                <div class="okay_list_heading okay_list_check">
                                    <input class="hidden_check fn_check_all" type="checkbox" id="check_all_2" name="" value=""/>
                                    <label class="okay_ckeckbox" for="check_all_2"></label>
                                </div>
                                <div class="okay_list_option">
                                    <select name="action" class="selectpicker form-control">
                                        <option value="enable">{$btr->general_do_enable|escape}</option>
                                        <option value="disable">{$btr->general_do_disable|escape}</option>
                                        <option value="delete">{$btr->general_delete|escape}</option>
                                        <option value="duplicate">{$btr->general_create_dublicate|escape}</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    {else}
        <div class="heading_box mt-1">
            <div class="text_grey">{$btr->sviat_header_notice_bar_no_items|escape}</div>
        </div>
    {/if}
</div>
