<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class Boolean implements Modifier
{
    public function title(): string
    {
        return 'Booleà';
    }

    public function description(): string
    {
        return "Converteix el valor a un booleà estrict. Es consideren falsos els següents valors:
        false, 'false', 0, '0', '', 'off', 'no', null
        Tot el demés es considera cert.";
    }

    public function settings(): array
    {
        return [];
    }

    public function handle($value = null, array $settings = []): bool
    {
        switch (strtolower($value)) {
            case false:
            case 'false':
            case 0:
            case '0':
            case '':
            case 'off':
            case 'no':
            case null:
                return false;
                break;
            default:
                return true;
                break;
        }
    }
}
