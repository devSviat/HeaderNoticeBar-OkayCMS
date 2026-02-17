{if $banner->id}
    {$meta_title = $banner->name scope=global}
{else}
    {$meta_title = $btr->sviat_header_notice_bar_new scope=global}
{/if}

{*Назва сторінки*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {if !$banner->id}
                    {$btr->sviat_header_notice_bar_new|escape}
                {else}
                    {$banner->name|escape}
                {/if}
            </div>
        </div>
    </div>
</div>

{*Вивід успішних повідомлень*}
{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="alert alert--center alert--icon alert--success">
                <div class="alert__content">
                    <div class="alert__title">
                        {if $message_success == 'added'}
                            {$btr->sviat_header_notice_bar_added|escape}
                        {elseif $message_success == 'updated'}
                            {$btr->sviat_header_notice_bar_updated|escape}
                        {/if}
                    </div>
                </div>
                {if $smarty.get.return}
                    <a class="alert__button" href="{$smarty.get.return|escape:'url'}">
                        {include file='svg_icon.tpl' svgId='return'}
                        <span>{$btr->general_back|escape}</span>
                    </a>
                {/if}
            </div>
        </div>
    </div>
{/if}

{*Головна форма сторінки*}
<form method="post" enctype="multipart/form-data" class="fn_fast_button">
    <input type="hidden" name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="lang_id" value="{$lang_id}" />
    <input name="id" type="hidden" value="{$banner->id|escape}" />
    <div class="row">
        {*Ліва частина - Назва та Контент*}
        <div class="col-lg-8 col-md-12">
            <div class="boxed">
                {*Назва елемента сайту*}
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="heading_label">
                            {$btr->general_name|escape}
                        </div>
                        <div class="form-group">
                            <input class="form-control mb-h" name="name" type="text" value="{$banner->name|escape}" />
                        </div>
                    </div>
                </div>

                {*Контент банера*}
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <textarea name="content" id="fn_editor"
                            class="editor_small">{$banner->content|escape}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {*Права частина - Налаштування*}
        <div class="col-lg-4 col-md-12">
            <div class="boxed">
                {*Видимість елемента*}
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="activity_of_switch activity_of_switch--left mt-q mb-1">
                            <div class="activity_of_switch_item">
                                <div class="okay_switch okay_switch--nowrap clearfix">
                                    <label class="switch_label">{$btr->general_enable|escape}</label>
                                    <label class="switch switch-default">
                                        <input class="switch-input" name="visible" value='1' type="checkbox"
                                            id="visible_checkbox" {if $banner->visible}checked="" {/if} />
                                        <span class="switch-label"></span>
                                        <span class="switch-handle"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {*Тип фону*}
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="heading_label">
                            {$btr->sviat_header_notice_bar_background_type|escape}
                        </div>
                        <div class="form-group">
                            <div class="okay_type_radio_wrap" style="display: inline-block; margin-right: 15px;">
                                <input id="background_type_color" class="hidden_check" name="background_type" type="radio" value="color"
                                    {if !$banner->background_type || $banner->background_type == 'color'}checked=""{/if} />
                                <label for="background_type_color" class="okay_type_radio">
                                    <span>{$btr->sviat_header_notice_bar_background_type_color|escape}</span>
                                </label>
                            </div>
                            <div class="okay_type_radio_wrap" style="display: inline-block;">
                                <input id="background_type_gradient" class="hidden_check" name="background_type" type="radio" value="gradient"
                                    {if $banner->background_type == 'gradient'}checked=""{/if} />
                                <label for="background_type_gradient" class="okay_type_radio">
                                    <span>{$btr->sviat_header_notice_bar_background_type_gradient|escape}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {*Колір фону*}
                <div class="row fn_background_color_block" {if $banner->background_type == 'gradient'}style="display: none;"{/if}>
                    <div class="col-lg-12 col-md-12">
                        <div class="heading_label">
                            {$btr->sviat_header_notice_bar_background_color|escape}
                        </div>
                        <div class="form-group">
                            <input class="form-control" name="background_color" type="color"
                                value="{if $banner->background_color}{$banner->background_color|escape}{else}#ffffff{/if}" />
                        </div>
                    </div>
                </div>

                {*Градієнт фону*}
                <div class="row fn_background_gradient_block" {if !$banner->background_type || $banner->background_type == 'color'}style="display: none;"{/if}>
                    <div class="col-lg-12 col-md-12">
                        <div class="heading_label">
                            {$btr->sviat_header_notice_bar_background_gradient|escape}
                        </div>
                        <div class="form-group">
                            <div class="row mb-h">
                                <div class="col-lg-6 col-md-6">
                                    <label class="small">{$btr->sviat_header_notice_bar_gradient_color_from|escape}</label>
                                    <input class="form-control" name="gradient_color_from" type="color"
                                        value="{if $banner->gradient_color_from}{$banner->gradient_color_from|escape}{else}#a3d2ff{/if}" />
                                </div>
                                <div class="col-lg-6 col-md-6">
                                    <label class="small">{$btr->sviat_header_notice_bar_gradient_color_to|escape}</label>
                                    <input class="form-control" name="gradient_color_to" type="color"
                                        value="{if $banner->gradient_color_to}{$banner->gradient_color_to|escape}{else}#fff2a6{/if}" />
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="small">{$btr->sviat_header_notice_bar_custom_gradient|escape}</label>
                                <input class="form-control" name="background_gradient" type="text"
                                    value="{if $banner->background_gradient}{$banner->background_gradient|escape}{/if}"
                                    placeholder="linear-gradient(90deg, #0e7a0c 0%, #1dc921 45%, #0e7a0c 100%)" />
                                <small class="text-muted">{$btr->sviat_header_notice_bar_gradient_hint|escape}</small>
                            </div>
                        </div>
                    </div>
                </div>

                {*Дати публікації*}
                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="heading_label">
                            {$btr->sviat_header_notice_bar_publish_from|escape}
                        </div>
                        <div class="form-group">
                            <input class="form-control" name="publish_from" type="datetime-local"
                                value="{if $banner->publish_from}{$banner->publish_from|date_format:"%Y-%m-%dT%H:%M"}{/if}" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 col-md-12">
                        <div class="heading_label">
                            {$btr->sviat_header_notice_bar_publish_to|escape}
                        </div>
                        <div class="form-group">
                            <input class="form-control" name="publish_to" type="datetime-local"
                                value="{if $banner->publish_to}{$banner->publish_to|date_format:"%Y-%m-%dT%H:%M"}{/if}" />
                        </div>
                    </div>
                </div>

                {*Кнопки збереження*}
                <div class="row">
                    <div class="col-lg-12 col-md-12 mt-1">
                        <button type="submit" class="btn btn_small btn_blue w-100">
                            {include file='svg_icon.tpl' svgId='checked'}
                            <span>{$btr->general_apply|escape}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{* Підключаємо Tiny MCE *}
{include file='tinymce_init.tpl'}

<script>
    (function() {
        var colorRadio = document.getElementById('background_type_color');
        var gradientRadio = document.getElementById('background_type_gradient');
        var colorBlock = document.querySelector('.fn_background_color_block');
        var gradientBlock = document.querySelector('.fn_background_gradient_block');
        
        if (!colorRadio || !gradientRadio || !colorBlock || !gradientBlock) {
            return;
        }
        
        function toggleBlocks() {
            var isColor = colorRadio.checked;
            colorBlock.style.display = isColor ? '' : 'none';
            gradientBlock.style.display = isColor ? 'none' : '';
        }
        
        colorRadio.addEventListener('change', toggleBlocks);
        gradientRadio.addEventListener('change', toggleBlocks);
    })();
</script>