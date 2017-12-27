<?php

namespace Tele2LtSmsApi\Client;

use Tele2LtSmsApi\Exception\AdapterException;
use Tele2LtSmsApi\Model\Cookie;
use Tele2LtSmsApi\Model\Header;
use Tele2LtSmsApi\Model\HttpResponse;

class Adapter
{
    const HTTP_LINE_END = "\r\n";

    private const STATIC_HEADERS = [
        'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: lt-LT,lt;q=0.5',
        'X-User-Agent: Tele2LtSmsApi Library',
    ];

    /**
     * @var array
     */
    private $headerCallbackStorage;

    /**
     * @var string
     */
    private $referer;

    public function query(string $url, array $cookies = []): HttpResponse
    {
        $ch = curl_init($url);

        return $this->request($ch, [], $cookies);
    }

    public function post(string $url, array $params, array $cookies = []): HttpResponse
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);

        $body = http_build_query($params);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $headers = [
            'Content-Length: ' . strlen($body),
        ];

        return $this->request($ch, $headers, $cookies);
    }

    private function request($ch, array $headers, array $cookies): HttpResponse
    {
        curl_setopt($ch, CURLOPT_COOKIEFILE, '');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $this->headerCallbackStorage = [];
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'headerCallback']);

        if (!empty($cookies)) {
            $headers[] = $this->buildCookieHeader($cookies);
        }

        if ($this->referer) {
            $headers[] = 'Referer: ' . $this->referer;
        }

        $headers = array_merge(self::STATIC_HEADERS, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $body = curl_exec($ch);
        if (false === $body) {
            throw new AdapterException('Request failed. Reason: ' . curl_error($ch));
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        $this->referer = $url;
        curl_close($ch);

        return new HttpResponse($status, $body, $this->headerCallbackStorage, $url);
    }

    protected function headerCallback($ch, $header): int
    {
        $length = strlen($header);
        $header = trim($header);
        if ('' !== $header) {
            if ('HTTP/' === substr($header, 0, 5)) {
                // Collect only headers of the last redirect
                $this->headerCallbackStorage = [];
            } else {
                $parts = explode(':', $header, 2);
                $value = $parts[1] ?? '';
                $this->headerCallbackStorage[] = new Header($parts[0], trim($value));
            }
        }

        return $length;
    }

    private function buildCookieHeader(array $cookies): string
    {
        $build = [];
        /** @var Cookie $cookie */
        foreach ($cookies as $cookie) {
            $build[$cookie->getName()] = $cookie->getValue();
        }

        return 'Cookie: ' . http_build_query($build, '', '; ');
    }
}