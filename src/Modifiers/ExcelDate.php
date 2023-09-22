<?php

namespace SimonHamp\LaravelNovaCsvImport\Modifiers;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use SimonHamp\LaravelNovaCsvImport\Contracts\Modifier;

class ExcelDate implements Modifier
{
    public function title(): string
    {
        return "Analitzador de Dates d'Excel";
    }

    public function description(): string
    {
        return "Interpreta el valor proporcionat com una data-hora decimal d'Excel i el converteix en un objecte DateTime i el formateja segons l'opció format subministrada.";
    }

    public function settings(): array
    {
        return [
            'format' => [
                'type' => 'string',
                'default' => 'Y-m-d H:i:s',
            ],
        ];
    }

    public function handle($value = null, array $settings = []): string
    {
        return Date::excelToDateTimeObject($value)
            ->format($settings['format'] ?? $this->settings()['format']['default']);
    }
}
