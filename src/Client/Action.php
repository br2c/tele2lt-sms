<?php

namespace Tele2LtSmsApi\Client;

use Tele2LtSmsApi\Exception\RemoteErrorException;
use Tele2LtSmsApi\Exception\BadResponseException;
use Tele2LtSmsApi\Model\Account;
use Tele2LtSmsApi\Model\HttpResponse;
use Tele2LtSmsApi\Model\Session;

class Action extends ClientAbstract
{
    public function sendSms(Session $session, string $number, string $message): void
    {
        $response = $this->doQuery($session->getMainUrl(), $session);
        $body = $response->getBody();

        $params = [
            self::TOKEN_INPUT_NAME => $this->findSendSmsFormToken($body),
            self::RECIPIENTS_INPUT_NAME => $number,
            self::MESSAGE_TEXT_INPUT_NAME => $message,
        ];

        $url = $this->findSendSmsUrl($body);
        $response = $this->doPost($this->relativeUrlToUrl($url), $params, $session);
        $this->checkSendSmsResponse($response);
    }

    public function getAccount(Session $session): Account
    {
        $response = $this->doQuery($session->getMainUrl(), $session);
        $body = $response->getBody();

        $account = new Account();
        $account->setFreeSmsCount($this->findFreeSmsCount($body));
        $account->setSmsCharge($this->findSmsCharge($body));

        return $account;
    }

    private function findSendSmsUrl(string $body): string
    {
        if (!preg_match(self::SEND_SMS_URL_REGEXP, $body, $matches)) {
            throw new BadResponseException('Can not find send SMS URL');
        };

        return $matches[1];
    }

    private function findSendSmsFormToken(string $body): string
    {
        if (!preg_match(self::SEND_SMS_TOKEN_VALUE_REGEXP, $body, $matches)) {
            throw new BadResponseException("Can not find send SMS form's request validation token");
        };

        return $matches[2];
    }

    private function findFreeSmsCount(string $body): int
    {
        if (!preg_match(self::FREE_SMS_COUNT_REGEXP, $body, $matches)) {
            throw new BadResponseException('Can not find free SMS count');
        };

        return (int) $matches[1];
    }

    private function findSmsCharge(string $body): float
    {
        if (!preg_match(self::SMS_CHARGE_REGEXP, $body, $matches)) {
            throw new BadResponseException('Can not find SMS charge value');
        };

        return (float) $matches[1];
    }

    private function checkSendSmsResponse(HttpResponse $response): void
    {
        $message = json_decode($response->getBody());
        if (false === $message) {
            throw new BadResponseException('Send SMS response contains no valid JSON object');
        }

        if (!empty($message->ErrorMessage)) {
            throw new RemoteErrorException('Send SMS failed. Reason: ' . implode(', ', (array) $message->ErrorMessage));
        }

        if (empty($message->SuccessMessage)) {
            throw new BadResponseException('Send SMS response contains neither error nor success message');
        }
    }
}