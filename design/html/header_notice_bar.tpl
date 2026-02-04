{if !empty($header_notice_banners)}
    {foreach $header_notice_banners as $banner}
        <div class="header_notice_bar"
            style="{if $banner->background_type == 'gradient' && $banner->background_gradient}background: {$banner->background_gradient|escape};{elseif $banner->background_type == 'gradient' && $banner->gradient_color_from && $banner->gradient_color_to}background: linear-gradient(90deg, {$banner->gradient_color_from|escape}, {$banner->gradient_color_to|escape});{elseif $banner->background_color}background-color: {$banner->background_color|escape};{/if}">
            <div class="header_notice_bar__content">
                {$banner->content nofilter}
            </div>
        </div>
    {/foreach}
    <script>
        (function() {
            document.body.classList.add('has-header-notice-bar');
        })();
    </script>
{/if}