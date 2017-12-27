<?php

namespace Tele2LtSmsApi\Model;

class HttpResponse
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var Header[]
     */
    private $headers;

    /**
     * @var int
     */
    private $status;

    /**
     * @var string
     */
    private $url;

    public function __construct(int $status, string $body, array $headers, string $url)
    {
        $this->status = $status;
        $this->body = $body;
        $this->headers = $headers;
        $this->url = $url;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return Header[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isOkStatus(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    public function getHeader(string $name): ? Header
    {
        $headers = $this->getHeaders();
        foreach ($headers as $header) {
            if ($header->getName() === $name) {
                return $header;
            }
        }

        return null;
    }

    /**
     * @return Cookie[]
     */
    public function getCookies(): array
    {
        $cookies = [];

        foreach ($this->headers as $header) {
            if ('Set-Cookie' !== $header->getName()) {
                continue;
            }

            $value = trim($header->getValue());
            if ('' === $value) {
                continue;
            }

            $index = strpos($value, ';');
            if (false !== $index) {
                // Leave only cookie name and value part
                $value = substr($value, 0, $index);
            }

            $parts = explode('=', $value, 2);

            $cookies[] = new Cookie($parts[0], $parts[1] ?? '');
        }

        return $cookies;
    }
}