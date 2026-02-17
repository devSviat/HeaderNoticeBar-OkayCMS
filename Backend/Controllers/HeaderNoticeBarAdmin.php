<?php


namespace Okay\Modules\Sviat\HeaderNoticeBar\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\Sviat\HeaderNoticeBar\Entities\HeaderNoticeBarEntity;
use Okay\Modules\Sviat\HeaderNoticeBar\Init\Init;
use Okay\Modules\Sviat\HeaderNoticeBar\Requests\HeaderNoticeBarRequest;

class HeaderNoticeBarAdmin extends IndexAdmin
{
    public function fetch(
        HeaderNoticeBarEntity $bannersEntity,
        HeaderNoticeBarRequest $bannersRequest
    ) {
        if ($this->request->method('post')) {
            $displayMode = $this->request->post('header_notice_bar_display_mode');
            if ($displayMode !== null && in_array($displayMode, [Init::DISPLAY_MODE_RANDOM, Init::DISPLAY_MODE_SEQUENCE], true)) {
                $this->settings->set(Init::SETTING_DISPLAY_MODE, $displayMode);
            }
            $intervalMinutes = $this->request->post('header_notice_bar_interval_minutes', 'integer');
            if ($intervalMinutes !== null && $intervalMinutes >= 1 && $intervalMinutes <= 1440) {
                $this->settings->set(Init::SETTING_INTERVAL_MINUTES, $intervalMinutes);
            }

            $ids = $this->request->post('check');
            if (is_array($ids)) {
                $ids = array_map('intval', $ids);
                $ids = array_filter($ids, function ($id) { return $id > 0; });
                $action = $this->request->post('action');
                switch ($action) {
                    case 'disable':
                        $bannersEntity->update($ids, ['visible' => 0]);
                        break;
                    case 'enable':
                        $bannersEntity->update($ids, ['visible' => 1]);
                        break;
                    case 'delete':
                        $bannersEntity->delete($ids);
                        break;
                    case 'duplicate':
                        foreach ($ids as $id) {
                            $banner = $bannersEntity->get($id);
                            if ($banner) {
                                $newBanner = clone $banner;
                                unset($newBanner->id);
                                $newBanner->name = $banner->name . ' (копія)';
                                $newBanner->created_at = date('Y-m-d H:i:s');
                                $newBanner->updated_at = date('Y-m-d H:i:s');
                                $bannersEntity->add($newBanner);
                            }
                        }
                        break;
                }
            }
            
            $positions = $this->request->post('positions');
            if (!empty($positions) && is_array($positions)) {
                $ids = array_keys($positions);
                $posValues = array_values($positions);
                sort($posValues);
                foreach ($ids as $i => $id) {
                    $id = (int) $id;
                    if ($id > 0 && isset($posValues[$i])) {
                        $bannersEntity->update($id, ['position' => (int) $posValues[$i]]);
                    }
                }
            }
        }

        $displayMode = $this->settings->get(Init::SETTING_DISPLAY_MODE) ?: Init::DISPLAY_MODE_SEQUENCE;
        $intervalMinutes = $this->settings->get(Init::SETTING_INTERVAL_MINUTES);
        if ($intervalMinutes === null || (int)$intervalMinutes < 1) {
            $intervalMinutes = 5;
        }
        $this->design->assign('header_notice_bar_display_mode', $displayMode);
        $this->design->assign('header_notice_bar_interval_minutes', (int)$intervalMinutes);

        $banners = $bannersEntity->find();
        $this->design->assign('banners', $banners);

        $this->response->setContent($this->design->fetch('header_notice_bar.tpl'));
    }
}
