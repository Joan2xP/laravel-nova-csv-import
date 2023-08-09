<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class MoneytoNum implements Modifier
{
    public function title(): string
    {
        return 'Money to number';
    }

    public function description(): string
    {
        return "Turn money notation to number";
    }

    public function settings(): array
    {
        return [];
    }

    public function getAmount($money)
{
    $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
    $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

    $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

    $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
    $removedThousandSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '',  $stringWithCommaOrDot);

    return (float) str_replace(',', '.', $removedThousandSeparator);
}

    public function handle($value = null, array $settings = []): string
    {

        return $this->getAmount($value);
    }
}
