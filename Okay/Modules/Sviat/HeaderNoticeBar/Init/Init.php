<?php


namespace Okay\Modules\Sviat\HeaderNoticeBar\Init;

use Okay\Core\Modules\AbstractInit;
use Okay\Core\Modules\EntityField;
use Okay\Helpers\MainHelper;
use Okay\Modules\Sviat\HeaderNoticeBar\Entities\HeaderNoticeBarEntity;
use Okay\Modules\Sviat\HeaderNoticeBar\Extenders\FrontExtender;

class Init extends AbstractInit
{
    const PERMISSION = 'sviat__header_notice_bar';

    public function install()
    {
        $this->setBackendMainController('HeaderNoticeBarAdmin');

        $this->migrateEntityTable(HeaderNoticeBarEntity::class, [
            (new EntityField('id'))->setIndexPrimaryKey()->setTypeInt(11, false)->setAutoIncrement(),
            (new EntityField('name'))->setTypeVarchar(255)->setIsLang(),
            (new EntityField('content'))->setTypeText()->setIsLang(),
            (new EntityField('background_type'))->setTypeVarchar(20)->setDefault('color'),
            (new EntityField('background_color'))->setTypeVarchar(7)->setDefault('#ffffff'),
            (new EntityField('background_gradient'))->setTypeVarchar(500)->setNullable(),
            (new EntityField('gradient_color_from'))->setTypeVarchar(7)->setNullable(),
            (new EntityField('gradient_color_to'))->setTypeVarchar(7)->setNullable(),
            (new EntityField('visible'))->setTypeTinyInt(1, true)->setDefault(1)->setIndex(),
            (new EntityField('position'))->setTypeInt(11)->setDefault(0)->setIndex(),
            (new EntityField('publish_from'))->setTypeDatetime(true),
            (new EntityField('publish_to'))->setTypeDatetime(true),
            (new EntityField('created_at'))->setTypeDatetime(false),
            (new EntityField('updated_at'))->setTypeDatetime(false),
        ]);

        $this->registerEntityLangInfo(HeaderNoticeBarEntity::class, 'sviat__header_notice_bar', 'banner');
    }

    public function init()
    {
        $this->registerBackendController('HeaderNoticeBarAdmin');
        $this->registerBackendController('HeaderNoticeBarBannerAdmin');
        $this->addBackendControllerPermission('HeaderNoticeBarAdmin', self::PERMISSION);
        $this->addBackendControllerPermission('HeaderNoticeBarBannerAdmin', self::PERMISSION);

        $this->registerQueueExtension(
            [MainHelper::class, 'commonAfterControllerProcedure'],
            [FrontExtender::class, 'assignCurrentBanners']
        );

        $this->extendBackendMenu('sviat_left_header_notice_bar', [
            'sviat_left_header_notice_bar' => ['HeaderNoticeBarAdmin', 'HeaderNoticeBarBannerAdmin'],
        ], '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16.75 4.16699C16.75 3.66073 16.3393 3.25 15.833 3.25H4.16699C3.66073 3.25 3.25 3.66073 3.25 4.16699V15.833C3.25 16.3393 3.66073 16.75 4.16699 16.75H15.833C16.3393 16.75 16.75 16.3393 16.75 15.833V4.16699ZM18.25 15.833C18.25 17.1677 17.1677 18.25 15.833 18.25H4.16699C2.8323 18.25 1.75 17.1677 1.75 15.833V4.16699C1.75 2.8323 2.8323 1.75 4.16699 1.75H15.833C17.1677 1.75 18.25 2.8323 18.25 4.16699V15.833Z" fill="currentColor"/>
                <path d="M17.5 6.75C17.9142 6.75 18.25 7.08579 18.25 7.5C18.25 7.91421 17.9142 8.25 17.5 8.25H2.5C2.08579 8.25 1.75 7.91421 1.75 7.5C1.75 7.08579 2.08579 6.75 2.5 6.75H17.5Z" fill="currentColor"/>
                <path d="M7.50781 4.27539C7.92193 4.2755 8.25781 4.61124 8.25781 5.02539C8.25781 5.43953 7.92193 5.77528 7.50781 5.77539H7C6.58579 5.77539 6.25001 5.4396 6.25 5.02539C6.25 4.61118 6.58579 4.27539 7 4.27539H7.50781ZM13.4209 4.25C13.8351 4.25 14.1709 4.58579 14.1709 5C14.1709 5.41421 13.8351 5.75 13.4209 5.75H9.71582C9.30161 5.75 8.96582 5.41421 8.96582 5C8.96582 4.58579 9.30161 4.25 9.71582 4.25H13.4209Z" fill="currentColor"/>
            </svg>'
        );

        $this->extendUpdateObject(
            'sviat__header_notice_bar',
            self::PERMISSION,
            HeaderNoticeBarEntity::class
        );
    }
}
