<?php


namespace Okay\Modules\Sviat\HeaderNoticeBar\Extenders;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Modules\Extender\ExtensionInterface;
use Okay\Modules\Sviat\HeaderNoticeBar\Entities\HeaderNoticeBarEntity;

class FrontExtender implements ExtensionInterface
{
    private $entityFactory;
    private $design;
    private $activeBanners = null;

    public function __construct(EntityFactory $entityFactory, Design $design)
    {
        $this->entityFactory = $entityFactory;
        $this->design = $design;
    }

    public function assignCurrentBanners()
    {
        $activeBanners = $this->getActiveBanners();
        
        if (count($activeBanners) > 1) {
            $selectedBanner = $this->selectBannerForDisplay($activeBanners);
            $activeBanners = $selectedBanner ? [$selectedBanner] : [];
        }

        $this->design->assign('header_notice_banners', $activeBanners);
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

    private function selectBannerForDisplay(array $banners)
    {
        $bannersCount = count($banners);
        
        if ($bannersCount === 0) {
            return null;
        }

        if ($bannersCount === 1) {
            return $banners[0];
        }

        $nextIndex = array_rand($banners);
        return $banners[$nextIndex];
    }
}
