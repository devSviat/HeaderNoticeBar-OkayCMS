<?php

namespace Modules\Sviat\HeaderNoticeBar;

use Okay\Modules\Sviat\HeaderNoticeBar\Backend\Controllers\HeaderNoticeBarBannerAdmin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * ?return= — це шлях /backend/index.php?…, а |escape:'url' кодував у ньому кожен «/» і «?»:
 * href ставав відносним %2Fbackend%2F…, і «Назад» після збереження банера відкривав не список,
 * а головну адмінки. Для атрибута потрібне HTML-екранування, а не кодування частини адреси.
 */
class BackLinkTest extends TestCase
{
    public function testReturnLinkKeepsThePath(): void
    {
        $template = dirname((string) (new ReflectionClass(HeaderNoticeBarBannerAdmin::class))->getFileName(), 2)
            . '/design/html/header_notice_bar_banner.tpl';
        $source = (string) file_get_contents($template);

        preg_match_all('/href="\{\$smarty\.get\.return([^}]*)\}"/', $source, $matches);

        self::assertNotSame([], $matches[1], 'кнопки «Назад» з ?return= у шаблоні немає');
        foreach ($matches[1] as $modifiers) {
            self::assertSame('|escape', $modifiers);
        }
    }
}
