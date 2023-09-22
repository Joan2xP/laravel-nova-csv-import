<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use Illuminate\Support\Str as LaravelStr;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class Suffix implements Modifier
{
    public function title(): string
    {
        return 'Sufix';
    }

    public function description(): string
    {
        return 'Afegeix un sufix a cada fila amb el text proporcionat.';
    }

    public function settings(): array
    {
        return [
            'string' => [
                'type' => 'string',
                'title' => 'Suffix',
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return LaravelStr::finish($value, $settings['string']);
    }
}
