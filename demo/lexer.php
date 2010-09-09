<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../libs',
    get_include_path()
)));

require_once 'ParseInContext/Lexer.php';

$lexer = new \ParseInContext\Lexer(array(
    'number' => '[0-9]+',
    'plus' => '\+',
    'minus' => '\-',
    'multi' => '\*',
    'div' => '\/',
    'bracketOpen' => '\(',
    'bracketClose' => '\)'
));

var_dump($lexer->tokenize('3 + (5 / 6)'));
