<?php


namespace Okay\Modules\Sviat\HeaderNoticeBar;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Request;
use Okay\Core\Settings;
use Okay\Modules\Sviat\HeaderNoticeBar\Extenders\FrontExtender;
use Okay\Modules\Sviat\HeaderNoticeBar\Requests\HeaderNoticeBarRequest;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;

return [
    FrontExtender::class => [
        'class' => FrontExtender::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Design::class),
            new SR(Settings::class),
        ],
    ],
    HeaderNoticeBarRequest::class => [
        'class' => HeaderNoticeBarRequest::class,
        'arguments' => [
            new SR(Request::class),
        ],
    ],
];
