<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

return (new Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PSR12' => true,
        '@PSR12:risky' => true,
        'yoda_style' => ['always_move_variable' => true, 'equal' => false, 'identical' => false],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => true,
            'import_constants' => true,
        ],
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
        ],
        'phpdoc_separation' => [
            'skip_unlisted_annotations' => true,
        ],
        'concat_space' => [
            'spacing' => 'one',
        ],
        'not_operator_with_successor_space' => true,
        'phpdoc_to_comment' => [
            // Avoid converting generic trait uses and return type variable declaration from PHPDoc to comments
            'ignored_tags' => ['var', 'use'],
        ],
        'protected_to_private' => false,
        'native_constant_invocation' => ['strict' => false],
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => false],
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'modernize_strpos' => true,
        'get_class_to_class_keyword' => true,
        'operator_linebreak' => [
            'only_booleans' => true,
            'position' => 'end',
        ],
        'phpdoc_line_span' => [
            'const' => 'single',
            'property' => 'single',
            'method' => null,
        ],
        'visibility_required' => [
            'elements' => [
                'const', 'property', 'method',
            ],
        ],
        'class_attributes_separation' => [
            'elements' => ['method' => 'one', 'property' => 'only_if_meta'],
        ],
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        (new Finder())
            ->in([__DIR__ . '/src', __DIR__ . '/tests'])
            ->append([__FILE__])
            ->notPath('#Fixtures/TestDoubleReturnTag\.php#')
    );
