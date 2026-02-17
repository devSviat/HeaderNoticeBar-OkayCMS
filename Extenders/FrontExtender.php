<?php


namespace Okay\Modules\Sviat\HeaderNoticeBar\Extenders;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Core\Settings;
use Okay\Modules\Sviat\HeaderNoticeBar\Entities\HeaderNoticeBarEntity;
use Okay\Modules\Sviat\HeaderNoticeBar\Init\Init;

class FrontExtender implements ExtensionInterface
{
    private $entityFactory;
    private $design;
    private $settings;
    private $activeBanners = null;

    public function __construct(EntityFactory $entityFactory, Design $design, Settings $settings)
    {
        $this->entityFactory = $entityFactory;
        $this->design = $design;
        $this->settings = $settings;
    }

    public function assignCurrentBanners()
    {
        $activeBanners = $this->getActiveBanners();
        $displayMode = $this->settings->get(Init::SETTING_DISPLAY_MODE) ?: Init::DISPLAY_MODE_SEQUENCE;
        $intervalMinutes = (int)$this->settings->get(Init::SETTING_INTERVAL_MINUTES) ?: 5;
        if ($intervalMinutes < 1) {
            $intervalMinutes = 5;
        }
        $this->design->assign('header_notice_bar_display_mode', $displayMode);
        $this->design->assign('header_notice_bar_interval_minutes', $intervalMinutes);
        $this->design->assign('header_notice_banners', $activeBanners);

        $count = count($activeBanners);
        $initialIndex = 0;
        $cookieRaw = isset($_COOKIE['sviat_hnb']) ? (string)$_COOKIE['sviat_hnb'] : '';

        if ($count > 1 && $displayMode === Init::DISPLAY_MODE_SEQUENCE && $cookieRaw !== '') {
            $parts = explode(':', $cookieRaw, 2);
            if (count($parts) === 2) {
                $cookieIndex = (int)$parts[0];
                $cookieTimeMs = (int)$parts[1];
                $intervalMs = max(60000, $intervalMinutes * 60 * 1000);
                $nowMs = (int)round(microtime(true) * 1000);
                $elapsed = $nowMs - $cookieTimeMs;
                $steps = (int)floor($elapsed / $intervalMs);
                $initialIndex = ((($cookieIndex + $steps) % $count) + $count) % $count;
            }
        }
        $this->design->assign('header_notice_bar_initial_index', $initialIndex);
    }

    private function getActiveBanners()
    {
        if ($this->activeBanners !== null) {
            return $this->activeBanners;
        }

        $bannersEntity = $this->entityFactory->get(HeaderNoticeBarEntity::class);
        $this->activeBanners = $bannersEntity->order('position ASC')->find([
            'visible' => 1,
            'active' => true,
        ]);

        return $this->activeBanners;
    }
}
