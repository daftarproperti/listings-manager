<?php

namespace App\Helpers;

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class Dumper
{
    /**
     * Use VarDumper to dump variable into a string.
     * https://symfony.com/doc/current/components/var_dumper.html#dumpers
     */
    public static function dumpToString(mixed $variable): string
    {
        $cloner = new VarCloner();
        $dumper = new CliDumper();
        return type($dumper->dump($cloner->cloneVar($variable), true))->asString();
    }
}
