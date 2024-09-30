<?php

$finder = (new PhpCsFixer\Finder())
    ->in('app');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'single_quote' => true,
        'no_unused_imports' => true,
        'align_multiline_comment' => true,
    ])
    ->setFinder($finder);
