<?php

namespace Stillat\Numeral\Languages;

/*
 * Original notice
 * -------------------------------------------------------------
 *
 * numbro.js language configuration
 * language : Portuguese
 * locale : Brazil
 * author : Ramiro Varandas Jr : https://github.com/ramirovjr
 */

class Language_ptBR extends AbstractLanguage
{
    public static $code = 'pt-BR';

    protected $delimiters = [
        'thousands' => '.',
        'decimal' => ','
    ];

    protected $currency = [
        'symbol' => 'R$',
        'position' => 'prefix',
        'spaceSeparated' => true
    ];

    protected $abbreviations = [
        'thousand' => 'mil',
        'million' => 'milhões', // milhões
        'billion' => 'b',
        'trillion' => 't'
    ];

    public function ordinal($number)
    {
        return 'º';
    }

}