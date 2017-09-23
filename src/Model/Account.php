<?php

namespace Tele2LtSms\Model;

class Account
{
    /**
     * @var int
     */
    private $freeSmsCount;

    /**
     * @var float
     */
    private $smsCharge;

    public function getFreeSmsCount(): int
    {
        return $this->freeSmsCount;
    }

    public function setFreeSmsCount(int $freeSmsCount): void
    {
        $this->freeSmsCount = $freeSmsCount;
    }

    public function getSmsCharge(): float
    {
        return $this->smsCharge;
    }

    public function setSmsCharge(float $smsCharge): void
    {
        $this->smsCharge = $smsCharge;
    }
}