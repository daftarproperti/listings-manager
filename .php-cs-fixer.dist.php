<?php

$finder = (new PhpCsFixer\Finder())
    ->in('app');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);
