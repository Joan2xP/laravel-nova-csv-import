<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class DefaultValue implements Modifier
{
    public function title(): string
    {
        return 'Valor per defecte';
    }

    public function description(): string
    {
        return 'Estableix un valor per defecte per al camp si la columna del CSV està buida o falta.';
    }

    public function settings(): array
    {
        return [
            'string' => [
                'type' => 'string',
                'title' => 'Valor per defecte',
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return $value === null ? $settings['string'] : $value;
    }
}
