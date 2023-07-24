<?php

namespace WeDevBr\Bankly\Inputs;

use WeDevBr\Bankly\Support\Contracts\CustomerPhoneInterface;

/**
 * CustomerPhone class
 *
 * PHP version 7.4|8.0
 *
 * @author    WeDev Brasil Team <contato@wedev.software>
 * @author    Rafael Teixeira <rafaeldemeirateixeira@gmail.com>
 * @copyright 2020 We Dev Tecnologia Ltda
 * @link      https://github.com/wedevBr/bankly-laravel
 */
class CustomerPhone implements CustomerPhoneInterface
{
    /** @var string */
    protected string $countryCode;

    /** @var string */
    protected string $number;

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $countryCode
     * @return CustomerPhone
     */
    public function setCountryCode(string $countryCode): CustomerPhone
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    /**
     * @param string $number
     * @return CustomerPhone
     */
    public function setNumber(string $number): CustomerPhone
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'number' => $this->number,
            'countryCode' => $this->countryCode
        ];
    }
}
