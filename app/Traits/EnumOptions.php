<?php

namespace App\Traits;

trait EnumOptions
{
    /**
     * @return array<int, array<string, string>>
     */
    public static function options(): array
    {
        $cases   = static::cases();
        $options = [];
        foreach ($cases as $case) {
            $options[] = [
                'value' => $case->value,
                'label' => $case->display(),
            ];
        }
        return $options;
    }
}
