<?php

declare(strict_types=1);

$finder =
    PhpCsFixer\Finder::create()
        ->in([
            __DIR__ . '/src',
            __DIR__ . '/tests',
            __DIR__ . '/config',
        ])
        ->notName('*.blade.php')
;

$parallelConfig = PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect();

$config = new PhpCsFixer\Config();

return $config->setRules([
    '@PSR12'                              => true,
    '@PhpCsFixer'                         => true,
    'strict_param'                        => true,
    'array_syntax'                        => ['syntax' => 'short'],
    'ordered_imports'                     => ['sort_algorithm' => 'length'],
    'binary_operator_spaces'              => ['default' => 'align'],
    'return_assignment'                   => false,
    'concat_space'                        => ['spacing' => 'one'],
    'single_line_comment_style'           => ['comment_types' => ['hash']],
    'declare_strict_types'                => true,
    'php_unit_test_class_requires_covers' => false,
    'php_unit_method_casing'              => ['case' => 'snake_case'],
    'trailing_comma_in_multiline'         => ['elements' => ['arguments', 'arrays', 'match', 'parameters']],
    'new_with_parentheses'                => ['anonymous_class' => false, 'named_class' => true],
    'yoda_style'                          => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
])->setFinder($finder)->setParallelConfig($parallelConfig);
