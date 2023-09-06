<?php

namespace WeDevBr\Bankly\Types\Pix;

use Illuminate\Contracts\Support\Arrayable;
use WeDevBr\Bankly\Validators\Pix\PixQrCodeDataValidator;

class PixQrCodeData implements Arrayable
{
    /** @var string|null */
    public ?string $encodedValue;

    /** @var string|null */
    public ?string $documentNumber;

    /**
     * This validate and return an array
     * @return array
     */
    public function toArray(): array
    {
        $this->validate();
        return (array) $this;
    }

    /**
     * This function validate the PixQrCodeData type
     *
     * @return void
     */
    public function validate(): void
    {
        $pixCashout = new PixQrCodeDataValidator($this);
        $pixCashout->validate();
    }
}
