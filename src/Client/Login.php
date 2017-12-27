<?php

namespace Tele2LtSmsApi\Client;

use Tele2LtSmsApi\Exception\RemoteErrorException;
use Tele2LtSmsApi\Exception\BadResponseException;
use Tele2LtSmsApi\Model\HttpResponse;
use Tele2LtSmsApi\Model\Session;

class Login extends ClientAbstract
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $token;

    public function createSession(string $username, string $password): Session
    {
        $this->init();

        $this->login($username, $password);

        return $this->session;
    }

    private function init(): void
    {
        $this->session = new Session();

        $response = $this->doQuery(self::BASE_URL, $this->session);

        if (!preg_match(self::LOGIN_TOKEN_VALUE_REGEXP, $response->getBody(), $matches)) {
            throw new BadResponseException("Can not find login form's request validation token");
        };

        $this->token = $matches[1];
    }

    private function login(string $username, string $password): void
    {
        $params = [
            self::USERNAME_INPUT_NAME => $username,
            self::PASSWORD_INPUT_NAME => $password,
            self::TOKEN_INPUT_NAME => $this->token,
        ];

        $response = $this->doPost(self::LOGIN_URL, $params, $this->session);

        $url = $this->buildRedirectUrl($response);
        $response = $this->doQuery($url, $this->session);

        $this->session->setMainUrl($response->getUrl());
    }

    private function buildRedirectUrl(HttpResponse $response): string
    {
        $message = json_decode($response->getBody());
        if (false === $message) {
            throw new BadResponseException('Login response contains no valid JSON object');
        }

        if (!empty($message->ErrorMessage)) {
            throw new RemoteErrorException('Login failed. Reason: ' . implode(', ', (array) $message->ErrorMessage));
        }

        if (!isset($message->Url)) {
            throw new BadResponseException('Login response contains no location');
        }

        return $this->relativeUrlToUrl($message->Url);
    }
}