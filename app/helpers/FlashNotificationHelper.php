<?php

declare(strict_types=1);

namespace App\Helpers;

class FlashNotificationHelper
{
    private string $backendKey;

    function __construct()
    {
        $this->backendKey = 'notifications';
    }


    public function success(string $content): FlashNotificationHelper
    {
        return $this->set('success', $content);
    }

    public function danger(string $content): FlashNotificationHelper
    {
        return $this->set('danger', $content);
    }

    public function warning(string $content): FlashNotificationHelper
    {
        return $this->set('warning', $content);
    }

    public function info(string $content): FlashNotificationHelper
    {
        return $this->set('info', $content);
    }


    public function set(string $type, string $content): FlashNotificationHelper
    {
        $this->ensureBackendCreated();

        $_SESSION[$this->backendKey][] = ['type' => $type, 'content' => $content];

        return $this;
    }

    public function get(): array
    {
        $this->ensureBackendCreated();

        return $_SESSION[$this->backendKey];
    }

    public function clear(): void
    {
        $this->ensureBackendCreated();

        unset($_SESSION[$this->backendKey]);
    }

    public function flush(\ArrayAccess &$container): void
    {
        $container[$this->backendKey] = $this->get();

        $this->clear();
    }


    protected function ensureBackendCreated(): void
    {
        if (empty($_SESSION[$this->backendKey]))
            $_SESSION[$this->backendKey] = [];
    }
}
