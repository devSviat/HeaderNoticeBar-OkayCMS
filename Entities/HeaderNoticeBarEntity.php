<?php


namespace Okay\Modules\Sviat\HeaderNoticeBar\Entities;

use Okay\Core\Entity\Entity;

class HeaderNoticeBarEntity extends Entity
{
    protected static $fields = [
        'id',
        'background_type',
        'background_color',
        'background_gradient',
        'gradient_color_from',
        'gradient_color_to',
        'visible',
        'position',
        'publish_from',
        'publish_to',
        'created_at',
        'updated_at',
    ];

    protected static $langFields = [
        'name',
        'content',
    ];

    protected static $defaultOrderFields = ['position ASC', 'id DESC'];
    protected static $table = 'sviat__header_notice_bar';
    protected static $tableAlias = 'hnb';
    protected static $langTable = 'sviat__header_notice_bar';
    protected static $langObject = 'banner';

    protected function filter__active($value)
    {
        if ($value) {
            $tableAlias = $this->getTableAlias();
            $this->select->where("({$tableAlias}.publish_from IS NULL OR {$tableAlias}.publish_from <= NOW())")
                ->where("({$tableAlias}.publish_to IS NULL OR {$tableAlias}.publish_to >= NOW())");
        }
    }
}
