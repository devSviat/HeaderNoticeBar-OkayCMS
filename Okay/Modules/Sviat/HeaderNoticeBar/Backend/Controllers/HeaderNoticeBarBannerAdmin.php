<?php


namespace Okay\Modules\Sviat\HeaderNoticeBar\Backend\Controllers;

use Okay\Admin\Controllers\IndexAdmin;
use Okay\Modules\Sviat\HeaderNoticeBar\Entities\HeaderNoticeBarEntity;
use Okay\Modules\Sviat\HeaderNoticeBar\Requests\HeaderNoticeBarRequest;

class HeaderNoticeBarBannerAdmin extends IndexAdmin
{
    public function fetch(
        HeaderNoticeBarEntity $bannersEntity,
        HeaderNoticeBarRequest $bannersRequest
    ) {
        if ($this->request->method('POST')) {
            $banner = $bannersRequest->postBanner();
            
            if (empty($banner->id)) {
                $banner->created_at = date('Y-m-d H:i:s');
                $banner->updated_at = date('Y-m-d H:i:s');
                if (empty($banner->position)) {
                    $banner->position = 0;
                }
                $banner->id = $bannersEntity->add($banner);
                
                if ($banner->id) {
                    $this->postRedirectGet->storeMessageSuccess('added');
                    $this->postRedirectGet->storeNewEntityId($banner->id);
                } else {
                    $this->postRedirectGet->storeMessageError('Помилка при додаванні банера');
                }
            } else {
                $banner->updated_at = date('Y-m-d H:i:s');
                $result = $bannersEntity->update($banner->id, (array)$banner);
                if ($result) {
                    $this->postRedirectGet->storeMessageSuccess('updated');
                } else {
                    $this->postRedirectGet->storeMessageError('Помилка при оновленні банера');
                }
            }
            
            $this->postRedirectGet->redirect();
        } else {
            $bannerId = $this->request->get('id', 'integer');
            $banner = $bannersEntity->get($bannerId);
        }

        $this->design->assign('banner', $banner);
        
        $this->response->setContent($this->design->fetch('header_notice_bar_banner.tpl'));
    }
}
