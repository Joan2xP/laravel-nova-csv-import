<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use Illuminate\Support\Str as LaravelStr;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class Prefix implements Modifier
{
    public function title(): string
    {
        return 'Prefix';
    }

    public function description(): string
    {
        return 'Afegeix un prefix a cada fila amb una cadena proporcionada.';
    }

    public function settings(): array
    {
        return [
            'string' => [
                'type' => 'string',
                'title' => 'Prefix',
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return LaravelStr::start($value, $settings['string']);
    }
}
