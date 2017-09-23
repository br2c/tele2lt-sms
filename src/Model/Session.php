<?php

namespace Tele2LtSms\Model;

class Session
{
    /**
     * @var Cookie[]
     */
    private $cookies = [];

    /**
     * @var string
     */
    private $mainUrl;

    /**
     * @return Cookie[]
     */
    public function getCookies(): array
    {
        return array_values($this->cookies);
    }

    public function addCookies(array $cookies): void
    {
        /** @var Cookie $cookie */
        foreach ($cookies as $cookie) {
            $this->cookies[$cookie->getName()] = $cookie;
        }
    }

    public function getMainUrl(): string
    {
        return $this->mainUrl;
    }

    public function setMainUrl(string $mainUrl): void
    {
        $this->mainUrl = $mainUrl;
    }
}