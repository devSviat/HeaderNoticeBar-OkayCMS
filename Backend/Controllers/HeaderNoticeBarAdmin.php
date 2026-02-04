<?php


namespace Okay\Modules\Sviat\HeaderNoticeBar\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\Sviat\HeaderNoticeBar\Entities\HeaderNoticeBarEntity;
use Okay\Modules\Sviat\HeaderNoticeBar\Requests\HeaderNoticeBarRequest;

class HeaderNoticeBarAdmin extends IndexAdmin
{
    public function fetch(
        HeaderNoticeBarEntity $bannersEntity,
        HeaderNoticeBarRequest $bannersRequest
    ) {
        if ($this->request->method('post')) {
            $ids = $this->request->post('check');
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
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
                sort($positions);
                foreach($positions as $i=>$position) {
                    $bannersEntity->update($ids[$i], ['position'=>$position]);
                }
            }
        }

        $banners = $bannersEntity->find();
        $this->design->assign('banners', $banners);

        $this->response->setContent($this->design->fetch('header_notice_bar.tpl'));
    }
}
