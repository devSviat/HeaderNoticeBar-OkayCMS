<?php

namespace Modules\Sviat\HeaderNoticeBar;

use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\Settings;
use Okay\Modules\Sviat\HeaderNoticeBar\Extenders\FrontExtender;
use Okay\Modules\Sviat\HeaderNoticeBar\Init\Init;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/SuperglobalIsolation.php';

use Modules\Sviat\HeaderNoticeBar\Support\SuperglobalIsolation;

/**
 * Вибір банера, який побачить відвідувач. Позиція в ротації зберігається в
 * куці `sviat_hnb` як «індекс:мітка_часу», тобто повністю під контролем
 * відвідувача. Індекс із неї йде прямо в масив банерів, тож захист від
 * від'ємного й завеликого значення тут не косметичний.
 */
class BannerRotationTest extends TestCase
{
    use SuperglobalIsolation;

    protected function setUp(): void
    {
        $this->snapshotSuperglobals();
        unset($_COOKIE['sviat_hnb']);
    }

    protected function tearDown(): void
    {
        $this->restoreSuperglobals();
    }

    /**
     * @param array<int, \stdClass> $banners
     * @return array<string, mixed> те, що поїхало в шаблон
     */
    private function assignedVars(array $banners, array $settings = []): array
    {
        $assigned = [];

        $design = $this->createStub(Design::class);
        $design->method('assign')->willReturnCallback(
            static function (string $name, $value) use (&$assigned): void {
                $assigned[$name] = $value;
            }
        );

        $settingsStub = $this->createStub(Settings::class);
        $settingsStub->method('get')->willReturnCallback(
            static fn (string $key) => $settings[$key] ?? null
        );

        $extender = new FrontExtender($this->createStub(EntityFactory::class), $design, $settingsStub);

        // getActiveBanners() віддає вже наповнений кеш, тож сутність і база
        // взагалі не залучаються.
        $reflected = new \ReflectionProperty(FrontExtender::class, 'activeBanners');
        if (PHP_VERSION_ID < 80100) { $reflected->setAccessible(true); }
        $reflected->setValue($extender, $banners);

        $extender->assignCurrentBanners();

        return $assigned;
    }

    /** @return array<int, \stdClass> */
    private static function banners(int $count): array
    {
        return array_map(static fn (int $i) => (object) ['id' => $i, 'name' => "Банер $i"], range(1, $count));
    }

    private function setCookie(int $index, int $minutesAgo): void
    {
        $_COOKIE['sviat_hnb'] = $index . ':' . (int) round((microtime(true) - $minutesAgo * 60) * 1000);
    }

    // --- інтервал -----------------------------------------------------------

    /**
     * Інтервал ротації не може бути коротшим за хвилину — інакше банер миготів
     * би на кожному перезавантаженні сторінки.
     */
    /** @dataProvider intervalProvider */
    #[DataProvider('intervalProvider')]
    public function testIntervalFallsBackToFiveMinutes(mixed $stored, int $expected): void
    {
        $vars = $this->assignedVars(
            self::banners(3),
            [Init::SETTING_INTERVAL_MINUTES => $stored]
        );

        self::assertSame($expected, $vars['header_notice_bar_interval_minutes']);
    }

    public static function intervalProvider(): array
    {
        return [
            'нормальний'      => [10, 10],
            'не задано'       => [null, 5],
            'нуль'            => [0, 5],
            'відʼємний'       => [-3, 5],
            'нечисловий'      => ['багато', 5],
        ];
    }

    public function testDisplayModeDefaultsToSequence(): void
    {
        $vars = $this->assignedVars(self::banners(3));

        self::assertSame(Init::DISPLAY_MODE_SEQUENCE, $vars['header_notice_bar_display_mode']);
    }

    // --- обчислення індексу -------------------------------------------------

    public function testWithoutACookieRotationStartsFromTheFirstBanner(): void
    {
        $vars = $this->assignedVars(self::banners(3));

        self::assertSame(0, $vars['header_notice_bar_initial_index']);
    }

    /** За один інтервал ротація зсувається рівно на один банер. */
    public function testIndexAdvancesByOneStepPerInterval(): void
    {
        $this->setCookie(0, 6);
        $vars = $this->assignedVars(self::banners(3), [Init::SETTING_INTERVAL_MINUTES => 5]);

        self::assertSame(1, $vars['header_notice_bar_initial_index']);
    }

    /** Кілька пропущених інтервалів рахуються всі, а не один. */
    public function testMultipleMissedIntervalsAreCounted(): void
    {
        $this->setCookie(0, 26);
        $vars = $this->assignedVars(self::banners(3), [Init::SETTING_INTERVAL_MINUTES => 5]);

        self::assertSame(2, $vars['header_notice_bar_initial_index']); // 5 кроків по колу з трьох
    }

    /** Усередині інтервалу банер не змінюється. */
    public function testIndexIsStableWithinTheInterval(): void
    {
        $this->setCookie(1, 2);
        $vars = $this->assignedVars(self::banners(3), [Init::SETTING_INTERVAL_MINUTES => 5]);

        self::assertSame(1, $vars['header_notice_bar_initial_index']);
    }

    // --- захист від підробленої куки ---------------------------------------

    /**
     * Подвійне модуло `((($i + $s) % $n) + $n) % $n` існує саме заради цього:
     * від'ємний індекс із підробленої куки дає в PHP від'ємний залишок, і без
     * другого модуло в шаблон поїхав би неіснуючий ключ масиву.
     */
    /** @dataProvider hostileCookieProvider */
    #[DataProvider('hostileCookieProvider')]
    public function testIndexAlwaysLandsInsideTheBannerList(string $cookie): void
    {
        $_COOKIE['sviat_hnb'] = $cookie;
        $banners = self::banners(3);

        $index = $this->assignedVars($banners, [Init::SETTING_INTERVAL_MINUTES => 5])
            ['header_notice_bar_initial_index'];

        self::assertGreaterThanOrEqual(0, $index);
        self::assertLessThan(count($banners), $index);
    }

    public static function hostileCookieProvider(): array
    {
        $now = (int) round(microtime(true) * 1000);

        return [
            'відʼємний індекс'    => ['-7:' . $now],
            'величезний індекс'   => ['999999999:' . $now],
            'мітка з майбутнього' => ['1:' . ($now + 86400000)],
            'мітка нульова'       => ['1:0'],
            'сміття замість чисел' => ['абв:абв'],
        ];
    }

    /** Кука без роздільника ігнорується цілком — беремо перший банер. */
    /** @dataProvider malformedCookieProvider */
    #[DataProvider('malformedCookieProvider')]
    public function testMalformedCookieIsIgnored(string $cookie): void
    {
        $_COOKIE['sviat_hnb'] = $cookie;
        $vars = $this->assignedVars(self::banners(3));

        self::assertSame(0, $vars['header_notice_bar_initial_index']);
    }

    public static function malformedCookieProvider(): array
    {
        return [
            'без двокрапки' => ['просто-рядок'],
            'порожня'       => [''],
        ];
    }

    // --- вироджені випадки --------------------------------------------------

    /** З одним банером ротації немає — модуло на одиницю все одно дало б нуль. */
    public function testSingleBannerAlwaysShowsIndexZero(): void
    {
        $this->setCookie(5, 100);
        $vars = $this->assignedVars(self::banners(1), [Init::SETTING_INTERVAL_MINUTES => 5]);

        self::assertSame(0, $vars['header_notice_bar_initial_index']);
    }

    /**
     * Без банерів індекс лишається нулем і ділення на нуль не відбувається —
     * гілка обчислення взагалі не запускається при count <= 1.
     */
    public function testEmptyBannerListDoesNotDivideByZero(): void
    {
        $this->setCookie(3, 100);
        $vars = $this->assignedVars([], [Init::SETTING_INTERVAL_MINUTES => 5]);

        self::assertSame(0, $vars['header_notice_bar_initial_index']);
        self::assertSame([], $vars['header_notice_banners']);
    }

    /** У випадковому режимі кука не враховується — індекс обирає вже фронтенд. */
    public function testRandomModeIgnoresTheCookie(): void
    {
        $this->setCookie(2, 100);
        $vars = $this->assignedVars(
            self::banners(3),
            [Init::SETTING_DISPLAY_MODE => Init::DISPLAY_MODE_RANDOM, Init::SETTING_INTERVAL_MINUTES => 5]
        );

        self::assertSame(0, $vars['header_notice_bar_initial_index']);
    }
}
