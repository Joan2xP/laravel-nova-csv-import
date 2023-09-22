<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use Illuminate\Support\Str as LaravelStr;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class Str implements Modifier
{
    public function title(): string
    {
        return 'Modificadors de text';
    }

    public function description(): string
    {
        return "Alguns recursos per modificar el text";
    }

    public function settings(): array
    {
        return [
            'function' => [
                'type' => 'select',
                'options' => [
                    'ascii' => 'ASCII only',
                    'camel' => 'camelCase',
                    'kebab' => 'kebab-case',
                    'lcfirst' => 'lower Case First',
                    'lower' => 'lowercase',
                    'plural' => 'Pluralize',
                    'reverse' => 'esreveR',
                    'singular' => 'Singularize',
                    'slug' => 'slug-ify',
                    'snake' => 'sname_case',
                    'squish' => 'Squish',
                    'title' => 'Title Case',
                    'ucfirst' => 'Upper case first',
                    'upper' => 'UPPERCASE',
                ],
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        $function = $settings['function'];

        return LaravelStr::$function($value);
    }
}
