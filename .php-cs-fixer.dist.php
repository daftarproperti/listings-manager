<?php

$finder = (new PhpCsFixer\Finder())
    ->in('app');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'single_quote' => true,
        'no_unused_imports' => true,
        'align_multiline_comment' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arguments', 'array_destructuring', 'arrays', 'match', 'parameters'],
        ]
    ])
    ->setFinder($finder);
