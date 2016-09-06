<?php

namespace Stillat\Numeral\Languages;

/*
 * Original notice
 * -----------------------------------------------
 * 
 * numbro.js language configuration
 * language : English
 * locale: Australia
 * author : Benedikt Huss : https://github.com/ben305
 */

class Language_enAU extends AbstractLanguage
{

    public static $code = 'en-AU';

    protected $delimiters = [
        'thousands' => ',',
        'decimal' => '.'
    ];

    protected $currency = [
        'symbol' => '$',
        'position' => 'prefix'
    ];

    protected $abbreviations = [
        'thousand' => 'k',
        'million' => 'm',
        'billion' => 'b',
        'trillion' => 't'
    ];

    public function ordinal($number)
    {
        $b = intval($number % 10);
        if (intval($number % 100 / 10) === 1) {
            return 'th';
        } else {
            if ($b == 1) {
                return 'st';
            } elseif ($b == 2) {
                return 'nd';
            } elseif ($b == 3) {
                return 'rd';
            } else {
                return 'th';
            }
        }
    }

}