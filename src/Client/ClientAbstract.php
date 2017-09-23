<?php

namespace Tele2LtSms\Client;

use Tele2LtSms\Exception\BadResponseException;
use Tele2LtSms\Model\HttpResponse;
use Tele2LtSms\Model\Session;

abstract class ClientAbstract
{
    protected const BASE_URL = 'https://mano.tele2.lt/';
    protected const LOGIN_URL = self::BASE_URL . 'user/login';

    protected const USERNAME_INPUT_NAME = 'username';
    protected const PASSWORD_INPUT_NAME = 'password';
    protected const TOKEN_INPUT_NAME = '__RequestVerificationToken';
    protected const RECIPIENTS_INPUT_NAME = 'Recipients';
    protected const MESSAGE_TEXT_INPUT_NAME = 'MessageText';

    protected const TOKEN_VALUE_PATTERN =
        '<input name="__RequestVerificationToken" type="hidden" value="([[:alnum:]\-_]+)" />';
    protected const SEND_SMS_FORM_PATTERN = '<form data-url="([[:alnum:]\-_/]+)" id="SendSMSForm">';

    protected const LOGIN_TOKEN_VALUE_REGEXP = '~' . self::TOKEN_VALUE_PATTERN . '~';
    protected const SEND_SMS_URL_REGEXP = '~' . self::SEND_SMS_FORM_PATTERN . '~';
    protected const SEND_SMS_TOKEN_VALUE_REGEXP =
        '~' . self::SEND_SMS_FORM_PATTERN . '\s+' . self::TOKEN_VALUE_PATTERN . '~';
    protected const FREE_SMS_COUNT_REGEXP = '~<b id="smsCount">([1-9][0-9]*)</b>~';
    protected const SMS_CHARGE_REGEXP = '~<input type="hidden" id="smsCharge" value="(0\.[0-9]{1,2})" />~';

    /**
     * @var Adapter
     */
    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    protected function doQuery(string $url, Session $session): HttpResponse
    {
        $response = $this->adapter->query($url, $session->getCookies());
        if (!$response->isOkStatus()) {
            throw new BadResponseException("URL '{$url}' (query) responded with HTTP status '{$response->getStatus()}'.");
        }
        $session->addCookies($response->getCookies());

        return $response;
    }

    protected function doPost(string $url, array $params, Session $session): HttpResponse
    {
        $response = $this->adapter->post($url, $params, $session->getCookies());
        if (!$response->isOkStatus()) {
            throw new BadResponseException("URL '{$url}' (post) responded with HTTP status '{$response->getStatus()}'.");
        }

        $session->addCookies($response->getCookies());

        return $response;
    }

    protected function relativeUrlToUrl(string $path): string
    {
        return self::BASE_URL . ltrim($path, '/');
    }
}