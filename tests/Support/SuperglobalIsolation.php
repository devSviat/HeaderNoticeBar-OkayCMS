<?php

namespace Support;

/**
 * Знімає й відновлює суперглобали навколо тесту.
 *
 * Частина модулів читає $_SERVER / $_SESSION / $_COOKIE напряму (RedirectsExtension
 * бере HTTP_HOST і X-Forwarded-Proto, HeaderNoticeBar — куку ротації, бекенд-хелпери
 * тримають ліміт пагінації в сесії). Без відновлення один тест підтруює сусідній.
 *
 * Свідомо не #[BackupGlobals]: той серіалізує весь $GLOBALS, куди тут потрапляють
 * об'єкти Smarty і PDO — повільно і ламко.
 */
trait SuperglobalIsolation
{
    /** @var array<string, array<mixed>|null> */
    private array $superglobalSnapshot = [];

    protected function snapshotSuperglobals(): void
    {
        $this->superglobalSnapshot = [
            'SERVER'  => $_SERVER,
            'SESSION' => $_SESSION ?? null,
            'COOKIE'  => $_COOKIE,
            'GET'     => $_GET,
            'POST'    => $_POST,
        ];
    }

    protected function restoreSuperglobals(): void
    {
        if ($this->superglobalSnapshot === []) {
            return;
        }

        $_SERVER = $this->superglobalSnapshot['SERVER'];
        $_COOKIE = $this->superglobalSnapshot['COOKIE'];
        $_GET    = $this->superglobalSnapshot['GET'];
        $_POST   = $this->superglobalSnapshot['POST'];

        if ($this->superglobalSnapshot['SESSION'] === null) {
            unset($_SESSION);
        } else {
            $_SESSION = $this->superglobalSnapshot['SESSION'];
        }

        $this->superglobalSnapshot = [];
    }

    /**
     * Скидає статичну властивість класу.
     *
     * Тільки дваргументна форма setValue(): одноаргументна у PHP 8.5
     * задепрекейчена, а phpunit.xml має failOnDeprecation="true", тож такий
     * виклик завалив би тест.
     */
    protected function resetStaticProperty(string $class, string $property, mixed $value): void
    {
        $reflected = new \ReflectionProperty($class, $property);
        if (PHP_VERSION_ID < 80100) { $reflected->setAccessible(true); }
        if (PHP_VERSION_ID < 80100) { $reflected->setAccessible(true); }
        $reflected->setValue(null, $value);
    }
}
