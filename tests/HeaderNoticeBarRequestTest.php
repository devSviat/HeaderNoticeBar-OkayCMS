<?php

namespace Modules\Sviat\HeaderNoticeBar;

use Okay\Modules\Sviat\HeaderNoticeBar\Requests\HeaderNoticeBarRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Support/RequestStub.php';

use Support\RequestStub;

/**
 * Збір банера з форми адмінки. Кольори й градієнти звідси йдуть просто в
 * атрибут style на сторінці магазину, тож санітизація тут — це межа між
 * налаштуванням оформлення і вставкою довільного CSS/HTML відвідувачу.
 */
class HeaderNoticeBarRequestTest extends TestCase
{
    private function postBanner(array $post): \stdClass
    {
        return (new HeaderNoticeBarRequest(new RequestStub($post)))->postBanner();
    }

    private function sanitize(string $value): string
    {
        $reflected = new \ReflectionMethod(HeaderNoticeBarRequest::class, 'sanitizeCssValue');
        if (PHP_VERSION_ID < 80100) { $reflected->setAccessible(true); }
        return $reflected->invoke(new HeaderNoticeBarRequest(new RequestStub()), $value);
    }

    private function parseDatetime(mixed $value): ?string
    {
        $reflected = new \ReflectionMethod(HeaderNoticeBarRequest::class, 'parseDatetime');
        if (PHP_VERSION_ID < 80100) { $reflected->setAccessible(true); }
        return $reflected->invoke(new HeaderNoticeBarRequest(new RequestStub()), $value);
    }

    // --- санітизація CSS ----------------------------------------------------

    /**
     * Лапки, кутові дужки й зворотний слеш зрізаються — саме ними закривають
     * атрибут style і виходять у розмітку.
     */
    /** @dataProvider cssInjectionProvider */
    #[DataProvider('cssInjectionProvider')]
    public function testDangerousCharactersAreStripped(string $raw, string $expected): void
    {
        self::assertSame($expected, $this->sanitize($raw));
    }

    public static function cssInjectionProvider(): array
    {
        return [
            'вихід із атрибута'   => ['#fff" onload="alert(1)', '#fff onload=alert(1)'],
            'кутові дужки'        => ['red</style><script>', 'red/stylescript'],
            'одинарні лапки'      => ["url('evil')", 'url(evil)'],
            'зворотний слеш'      => ['\\0031', '0031'],
            'керівні символи'     => ["red\x00\x1f\n\t", 'red'],
            'нормальний колір'    => ['#ff8800', '#ff8800'],
            'нормальний градієнт' => [
                'linear-gradient(90deg, #fff 0%, #000 100%)',
                'linear-gradient(90deg, #fff 0%, #000 100%)',
            ],
        ];
    }

    /** Довжина обрізається, щоб роздутий style не поїхав на кожну сторінку. */
    public function testValueIsCappedAtFiveHundredCharacters(): void
    {
        self::assertSame(500, strlen($this->sanitize(str_repeat('a', 600))));
        self::assertSame(500, strlen($this->sanitize(str_repeat('a', 500))));
    }

    public function testEmptyValueStaysEmpty(): void
    {
        self::assertSame('', $this->sanitize(''));
    }

    // --- дати публікації ----------------------------------------------------

    public function testDatetimeIsNormalisedToDatabaseFormat(): void
    {
        self::assertSame('2025-08-05 14:30:00', $this->parseDatetime('2025-08-05 14:30'));
        self::assertSame('2025-08-05 00:00:00', $this->parseDatetime('05.08.2025'));
    }

    /**
     * Нерозпізнана дата стає null, а не 1970 роком: інакше банер із зіпсованим
     * полем «показувати з» опинився б у минулому й показався одразу.
     */
    /** @dataProvider unparsableDatetimeProvider */
    #[DataProvider('unparsableDatetimeProvider')]
    public function testUnparsableDatetimeBecomesNull(mixed $value): void
    {
        self::assertNull($this->parseDatetime($value));
    }

    public static function unparsableDatetimeProvider(): array
    {
        return [
            'null'           => [null],
            'порожній рядок' => [''],
            'самі пробіли'   => ['   '],
            'не дата'        => ['колись потім'],
        ];
    }

    // --- тип фону -----------------------------------------------------------

    /** Невідомий тип фону падає в «колір» — найбезпечніший варіант. */
    /** @dataProvider backgroundTypeProvider */
    #[DataProvider('backgroundTypeProvider')]
    public function testBackgroundTypeIsWhitelisted(mixed $posted, string $expected): void
    {
        self::assertSame($expected, $this->postBanner(['background_type' => $posted])->background_type);
    }

    public static function backgroundTypeProvider(): array
    {
        return [
            'колір'         => ['color', 'color'],
            'градієнт'      => ['gradient', 'gradient'],
            'вигаданий'     => ['image', 'color'],
            'не задано'     => [null, 'color'],
            'інший регістр' => ['GRADIENT', 'color'],
        ];
    }

    public function testMissingBackgroundColorFallsBackToWhite(): void
    {
        self::assertSame('#ffffff', $this->postBanner([])->background_color);
        self::assertSame('#ffffff', $this->postBanner(['background_color' => '   '])->background_color);
    }

    public function testBackgroundColorIsSanitised(): void
    {
        self::assertSame(
            '#fff onload=alert(1)',
            $this->postBanner(['background_color' => '#fff" onload="alert(1)'])->background_color
        );
    }

    // --- градієнт -----------------------------------------------------------

    /** Свій градієнт має пріоритет над парою кольорів, і пара тоді обнуляється. */
    public function testCustomGradientWinsOverTheColorPair(): void
    {
        $banner = $this->postBanner([
            'background_type'     => 'gradient',
            'background_gradient' => 'linear-gradient(90deg, #fff, #000)',
            'gradient_color_from' => '#111',
            'gradient_color_to'   => '#222',
        ]);

        self::assertSame('linear-gradient(90deg, #fff, #000)', $banner->background_gradient);
        self::assertNull($banner->gradient_color_from);
        self::assertNull($banner->gradient_color_to);
    }

    public function testColorPairIsUsedWhenNoCustomGradientGiven(): void
    {
        $banner = $this->postBanner([
            'background_type'     => 'gradient',
            'gradient_color_from' => '#111',
            'gradient_color_to'   => '#222',
        ]);

        self::assertNull($banner->background_gradient);
        self::assertSame('#111', $banner->gradient_color_from);
        self::assertSame('#222', $banner->gradient_color_to);
    }

    /**
     * Половина пари — це не градієнт. Усе обнуляється, інакше на сторінку
     * поїхав би незавершений linear-gradient і зламав верстку шапки.
     */
    /** @dataProvider incompleteGradientProvider */
    #[DataProvider('incompleteGradientProvider')]
    public function testIncompleteGradientIsDiscardedEntirely(array $post): void
    {
        $banner = $this->postBanner(['background_type' => 'gradient'] + $post);

        self::assertNull($banner->background_gradient);
        self::assertNull($banner->gradient_color_from);
        self::assertNull($banner->gradient_color_to);
    }

    public static function incompleteGradientProvider(): array
    {
        return [
            'лише перший колір' => [['gradient_color_from' => '#111']],
            'лише другий колір' => [['gradient_color_to' => '#222']],
            'нічого'            => [[]],
        ];
    }

    /** При типі «колір» градієнт не зберігається, навіть якщо його прислали. */
    public function testGradientIsIgnoredWhenTypeIsColor(): void
    {
        $banner = $this->postBanner([
            'background_type'     => 'color',
            'background_gradient' => 'linear-gradient(90deg, #fff, #000)',
            'gradient_color_from' => '#111',
            'gradient_color_to'   => '#222',
        ]);

        self::assertNull($banner->background_gradient);
        self::assertNull($banner->gradient_color_from);
        self::assertNull($banner->gradient_color_to);
    }

    // --- решта полів --------------------------------------------------------

    public function testNameIsCappedAtTheColumnWidth(): void
    {
        self::assertSame(255, strlen($this->postBanner(['name' => str_repeat('a', 300)])->name));
    }

    public function testVisibleIsAlwaysZeroOrOne(): void
    {
        self::assertSame(1, $this->postBanner(['visible' => '1'])->visible);
        self::assertSame(0, $this->postBanner([])->visible);
    }
}
