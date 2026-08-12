<?php

namespace Modules\Sviat\HeaderNoticeBar\Support;

use Okay\Core\Request;

/**
 * Ручний стаб замість мока: PHPUnit 13 узагалі відмовляється дублювати клас,
 * у якого є метод з іменем "method" — а саме так називається метод Request,
 * що віддає HTTP-метод запиту.
 *
 * Узагальнений варіант tests/Modules/Sviat/OrdersExport/RequestStub.php:
 * приймає мапи замість колбека, бо всі споживачі хочуть саме мапу.
 */
class RequestStub extends Request
{
    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $get
     * @param string|null $rawBody Те, що post() віддає БЕЗ аргументів. Частина
     *                             коду (напр. вебхук AutoDeploy) читає саме сире
     *                             тіло запиту й сама його розбирає.
     */
    public function __construct(
        private array $post = [],
        private array $get = [],
        private string $httpMethod = 'POST',
        private array $files = [],
        private ?string $rawBody = null,
    ) {
    }

    /**
     * Повторює контракт Okay\Core\Request::method(): без аргументу віддає назву
     * методу, з аргументом — булеве порівняння без урахування регістру.
     */
    public function method($method = null)
    {
        if ($method === null) {
            return $this->httpMethod;
        }

        return strtolower((string) $method) === strtolower($this->httpMethod);
    }

    public function post($name = null, $type = null, $default = null)
    {
        if ($name === null) {
            return $this->rawBody ?? $this->post;
        }

        return $this->post[$name] ?? $default;
    }

    public function get($name, $type = null, $default = null, $stripTags = true)
    {
        return $this->get[$name] ?? $default;
    }

    public function files($name, $name2 = null)
    {
        if ($name2 === null) {
            return $this->files[$name] ?? null;
        }

        return $this->files[$name][$name2] ?? null;
    }
}
