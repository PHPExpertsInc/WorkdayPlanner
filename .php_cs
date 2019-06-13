<?php

$header = <<<HEADER
This file is part of the Workday Planner, a PHP Experts, Inc., Project.

Copyright © 2018, 2019 PHP Experts, Inc.
Author: Theodore R. Smith <theodore@phpexperts.pro>
  GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
  https://www.phpexperts.pro/
  https://github.com/PHPExpertsInc/Skeleton

This file is licensed under the MIT License.
HEADER;

return PhpCsFixer\Config::create()
    ->setRules([
        '@Symfony'       => true,
        'elseif'         => false,
        //'braces'         => ['position_after_anonymous_constructs' => 'next'],
        'yoda_style'     => false,
        'list_syntax'    => ['syntax'  => 'short'],
        'concat_space'   => ['spacing' => 'one'],
        'binary_operator_spaces' => array(
            'align_double_arrow' => true,
        ),
        'phpdoc_no_alias_tag'          => false,
        'declare_strict_types'         => true,
        //'function_declaration'         => ['closure_function_spacing' => 'none'],
        'no_superfluous_elseif'        => true,
        'blank_line_after_opening_tag' => false,
        'header_comment' => [
            'header'       => $header,
            'location'     => 'after_declare_strict',
            'comment_type' => 'PHPDoc',
        ]
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('venodr')
            ->in(__DIR__)
    );
