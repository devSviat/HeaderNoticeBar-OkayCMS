{if !empty($header_notice_banners)}
    <div class="header_notice_bar_carousel{if $header_notice_banners|@count <= 1} header_notice_bar_carousel_ready{/if}" data-display-mode="{$header_notice_bar_display_mode|escape}" data-interval-minutes="{$header_notice_bar_interval_minutes|escape}">
        {foreach $header_notice_banners as $banner name="hnb"}
            <div class="header_notice_bar header_notice_bar_carousel__slide{if $smarty.foreach.hnb.iteration == $header_notice_bar_initial_index + 1} header_notice_bar_carousel__slide_active{/if}"
                style="{if $banner->background_type == 'gradient' && $banner->background_gradient}background: {$banner->background_gradient|escape};{elseif $banner->background_type == 'gradient' && $banner->gradient_color_from && $banner->gradient_color_to}background: linear-gradient(90deg, {$banner->gradient_color_from|escape}, {$banner->gradient_color_to|escape});{elseif $banner->background_color}background-color: {$banner->background_color|escape};{/if}">
                <div class="header_notice_bar__content">
                    {$banner->content nofilter}
                </div>
            </div>
        {/foreach}
    </div>
{literal}
    <script>
    (function () {
        'use strict';
        var STORAGE_KEY_INDEX = 'sviat_header_notice_bar_index';
        var STORAGE_KEY_TIME = 'sviat_header_notice_bar_time';
        var COOKIE_NAME = 'sviat_hnb';
        var COOKIE_MAX_AGE_DAYS = 1;

        function setSequenceCookie(index, timestamp) {
            var idx = parseInt(index, 10);
            var ts = parseInt(timestamp, 10);
            if (!Number.isFinite(idx) || !Number.isFinite(ts) || ts <= 0) return;
            var value = idx + ':' + ts;
            var maxAge = COOKIE_MAX_AGE_DAYS * 24 * 60 * 60;
            document.cookie = COOKIE_NAME + '=' + encodeURIComponent(value) + '; path=/; max-age=' + maxAge + '; SameSite=Lax';
        }

        function clampIndex(index, count) {
            if (count <= 0 || !Number.isFinite(count)) return 0;
            var i = parseInt(index, 10);
            if (!Number.isFinite(i)) return 0;
            i = ((i % count) + count) % count;
            return i >= 0 && i < count ? i : 0;
        }

        function initCarousel() {
            var carousel = document.querySelector('.header_notice_bar_carousel');
            if (!carousel) return;

            var allCookies = document.cookie || '';
            var match = allCookies.match(new RegExp('(?:^|;\\s*)' + COOKIE_NAME + '=([^;]*)'));
            var activeSlideIndex = -1;
            carousel.querySelectorAll('.header_notice_bar_carousel__slide').forEach(function (slide, idx) {
                if (slide.classList.contains('header_notice_bar_carousel__slide_active')) activeSlideIndex = idx;
            });

            var slides = carousel.querySelectorAll('.header_notice_bar_carousel__slide');
            var count = slides.length;
            if (count === 0) {
                carousel.classList.add('header_notice_bar_carousel_ready');
                return;
            }

            var displayMode = (carousel.getAttribute('data-display-mode') || 'sequence').trim();
            var intervalMinutes = parseInt(carousel.getAttribute('data-interval-minutes'), 10) || 5;
            if (intervalMinutes < 1) intervalMinutes = 1;
            var intervalMs = Math.max(60000, intervalMinutes * 60 * 1000);

            var currentIndexFromCookie = null;
            if (displayMode === 'sequence' && count > 1 && match) {
                try {
                    var parts = (match[1] ? decodeURIComponent(match[1]) : '').split(':');
                    if (parts.length === 2) {
                        var cookieIdx = parseInt(parts[0], 10);
                        var cookieTime = parseInt(parts[1], 10);
                        var now = Date.now();
                        if (!Number.isFinite(cookieIdx) || !Number.isFinite(cookieTime) || cookieTime > now || cookieTime < now - 7 * 24 * 60 * 60 * 1000) {
                            cookieIdx = 0;
                            cookieTime = now;
                        }
                        var elapsed = now - cookieTime;
                        var steps = Math.floor(elapsed / intervalMs);
                        if (!Number.isFinite(steps) || steps < 0) steps = 0;
                        var correctIndex = clampIndex(cookieIdx + steps, count);
                        currentIndexFromCookie = correctIndex;
                        if (correctIndex !== activeSlideIndex) {
                            for (var s = 0; s < slides.length; s++) {
                                slides[s].classList.toggle('header_notice_bar_carousel__slide_active', s === correctIndex);
                            }
                        }
                    }
                } catch (e) {}
            }

            function showSlide(index) {
                var i = clampIndex(index, count);
                slides.forEach(function (slide, idx) {
                    slide.classList.toggle('header_notice_bar_carousel__slide_active', idx === i);
                });
                return i;
            }

            function getNextIndex(currentIndex) {
                if (displayMode === 'random') {
                    if (count <= 1) return 0;
                    var next = Math.floor(Math.random() * count);
                    while (next === currentIndex && count > 1) {
                        next = Math.floor(Math.random() * count);
                    }
                    return next;
                }
                return (currentIndex + 1) % count;
            }

            var currentIndex = currentIndexFromCookie !== null ? currentIndexFromCookie : 0;
            if (count === 1) {
                showSlide(0);
                carousel.classList.add('header_notice_bar_carousel_ready');
                return;
            }

            if (displayMode === 'sequence') {
                if (currentIndexFromCookie === null) {
                    var stored = localStorage.getItem(STORAGE_KEY_INDEX);
                    var storedTime = localStorage.getItem(STORAGE_KEY_TIME);
                    var now = Date.now();
                    if (stored !== null && storedTime !== null) {
                        var lastTime = parseInt(storedTime, 10);
                        var elapsed = now - lastTime;
                        var steps = Number.isFinite(elapsed) && elapsed >= 0 ? Math.floor(elapsed / intervalMs) : 0;
                        if (steps < 0) steps = 0;
                        currentIndex = clampIndex(parseInt(stored, 10) + steps, count);
                        localStorage.setItem(STORAGE_KEY_INDEX, String(currentIndex));
                        localStorage.setItem(STORAGE_KEY_TIME, String(now));
                    }
                } else {
                    localStorage.setItem(STORAGE_KEY_INDEX, String(currentIndex));
                    localStorage.setItem(STORAGE_KEY_TIME, String(Date.now()));
                }
            } else {
                currentIndex = Math.floor(Math.random() * count);
            }

            showSlide(currentIndex);
            if (displayMode === 'sequence') {
                setSequenceCookie(currentIndex, Date.now());
            }

            setInterval(function () {
                currentIndex = getNextIndex(currentIndex);
                showSlide(currentIndex);
                if (displayMode === 'sequence') {
                    localStorage.setItem(STORAGE_KEY_INDEX, String(currentIndex));
                    localStorage.setItem(STORAGE_KEY_TIME, String(Date.now()));
                    setSequenceCookie(currentIndex, Date.now());
                }
            }, intervalMs);

            carousel.classList.add('header_notice_bar_carousel_ready');
        }

        try {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCarousel);
            } else {
                initCarousel();
            }
        } catch (e) {}
    })();
    </script>
{/literal}
{/if}
