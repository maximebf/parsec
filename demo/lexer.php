<?php

set_include_path(implode(PATH_SEPARATOR, array(
    __DIR__ . '/../lib',
    get_include_path()
)));

require_once 'Parsec/Lexer.php';

$lexer = new Parsec\Lexer(array(
    'number' => '[0-9]+',
    'plus' => '\+',
    'minus' => '\-',
    'multi' => '\*',
    'div' => '\/',
    'bracketOpen' => '\(',
    'bracketClose' => '\)'
));

var_dump($lexer->tokenize('3 + (5 / 6)'));
