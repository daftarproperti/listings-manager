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
    static function dumpToString(mixed $variable): string
    {
        $cloner = new VarCloner();
        $dumper = new CliDumper();
        return Assert::string($dumper->dump($cloner->cloneVar($variable), TRUE));
    }
}
