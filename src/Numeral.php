<?php

namespace Stillat\Numeral;

use Exception;
use Stillat\Numeral\Languages\EnglishLanguage;
use Stillat\Numeral\Languages\LanguageManager;
use Stillat\Numeral\Traits\JsonDecodeTrait;

class Numeral
{

    use JsonDecodeTrait;

    /**
     * The default zero format.
     *
     * @var null|string
     */
    protected $zeroFormat = null;

    protected $suffixes = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    protected $binarySuffixes = ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];

    protected $formatBinarySuffixes = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    protected $formatDecimalSuffixes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    /**
     * The LanguageManager instance.
     *
     * @var \Stillat\Numeral\Languages\LanguageManager
     */
    protected $languageManager;

    /**
     * Sets the LanguageManager instance.
     *
     * @param \Stillat\Numeral\Languages\LanguageManager $languageManager
     */
    public function setLanguageManager(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    /**
     * Gets the LanguageManager instance.
     *
     * @return \Stillat\Numeral\Languages\LanguageManager
     */
    public function getLanguageManager()
    {
        return $this->languageManager;
    }

    /**
     * Gets the zero format.
     *
     * @return string
     */
    public function getZeroFormat()
    {
        return $this->zeroFormat;
    }

    /**
     * Sets the zero-format.
     *
     * @param $format
     */
    public function setZeroFormat($format)
    {
        $this->zeroFormat = $format;
    }

    /**
     * Determines if a given string contains any of the needles.
     *
     * @param $haystack
     * @param $needles
     *
     * @return bool
     */
    private function stringContains($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines if a string starts with any of the given needles.
     *
     * @param $haystack
     * @param $needles
     *
     * @return bool
     */
    private function stringStartsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines if a string ends with any of the given needles.
     *
     * @param $haystack
     * @param $needles
     *
     * @return bool
     */
    private function stringEndsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ((string)$needle === substr($haystack, -strlen($needle))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines if a string is zero based on a set of rules.
     *
     * @param $string
     *
     * @return bool
     */
    protected function isZero($string)
    {
        if ($string === null || strlen($string) == 0 || $this->toFloat($string) == 0 || strtolower($string) == 'n/a') {
            return true;
        }

        return false;
    }

    /**
     * Adds padding based on the decimal value of the number.
     *
     * @param     $number
     * @param     $decimalPlaces
     * @param int $optionalCount
     *
     * @return string
     */
    protected function addPaddingToNumber($number, $decimalPlaces, $optionalCount = 0)
    {

        if ($this->stringContains($number, '.')) {
            $decimalPosition = $this->coalesce(strpos($number, '.'), -1);
            $decimalValue = $this->coalesce(substr($number, $decimalPosition + 1), -1);
            $currentDecimalLength = strlen($decimalValue);

            $paddingValue = $decimalPlaces - $currentDecimalLength - $optionalCount;
            if ($paddingValue > 0) {
                $paddingValue = str_repeat('0', $paddingValue);
            } else {
                $paddingValue = '';
            }

            return $number . $paddingValue;
        }

        if ($decimalPlaces - $optionalCount > 0) {
            return $number . '.' . str_repeat('0', $decimalPlaces - $optionalCount);
        }

        return $number;
    }

    /**
     * Implementation of toFixed for numbers with exponents.
     * 
     * This function may return negative representations of zero values e.g. "-0.0"
     *
     * @param mixed $number
     * @param int   $decimalPlaces
     *
     * @return string
     */
    protected function toFixedLargeSmall($number, $decimalPlaces)
    {
        $parts = explode('e', mb_strtolower((string) $number));

        $mantissa = $parts[0];
        $exponent = $parts[1];

        $beforeDecimal = '';
        $afterDecimal  = '';

        $prefix = '';
        $endString = '';
        $zerosString = '';
        $string = '';

        $mantissaParts = explode('.', $mantissa);

        if (count($mantissaParts) == 0) {
            $beforeDecimal = $mantissa;
        } elseif (count($mantissaParts) == 1) {
            $beforeDecimal = $mantissaParts[0];
        } elseif (count($mantissaParts) == 2) {
            $beforeDecimal = $mantissaParts[0];
            $afterDecimal  = $mantissaParts[1];
        }

        if (+$exponent > 0) {
            // Exponent is positive - add zeros after the numbers.
            $string = $beforeDecimal.$afterDecimal.str_repeat('0', $exponent - mb_strlen((string) $afterDecimal));
        } else {
            // Exponent is negative.
            
            if (+$beforeDecimal < 0) {
                $prefix = '-0';
            } else {
                $prefix = '0';
            }

            // Tack on the decimal point if needed.
            if ($decimalPlaces > 0) {
                $prefix .= '.';
            }

            $zerosString = str_repeat('0', ($exponent * -1) - 1);

            $endString = mb_substr(
                $zerosString.abs($beforeDecimal).$afterDecimal,
                0,
                $decimalPlaces
            );

            $string = $prefix.$endString;
        }


        if (+$exponent > 0 && $decimalPlaces > 0) {
            $string .= '.'.str_repeat('0', $decimalPlaces);
        }

        return $string;
    }

    /**
     * Converts a number to fixed notation, adding padding where necessary.
     *
     * @param     $number
     * @param     $decimalPlaces
     * @param int $optionalDigits
     *
     * @return string
     */
    protected function toFixed($number, $decimalPlaces, $optionalDigits = 0)
    {
        // Cast the number to a string.
        $stringNumber = mb_strtolower((string) $number);

        if ($this->stringContains($stringNumber, 'e')) {
            $number = $this->toFixedLargeSmall($number, $decimalPlaces);

            if ($this->stringStartsWith((string) $number, '-') && +$number >= 0) {
                $number = mb_substr($number, 1);
            }

            return $this->addPaddingToNumber($number, $decimalPlaces, $optionalDigits);
        }

        if ($this->stringContains($number, '.')) {

            // The number contains a decimal place, so we need to do some extra
            // processing.
            $decimalPosition = $this->coalesce(strpos($number, '.'), -1);
            $integerValue = $this->coalesce(substr($number, 0, $decimalPosition), -1);
            $decimalValue = $this->coalesce(substr($number, $decimalPosition + 1), -1);
            $currentDecimalLength = strlen($decimalValue);

            // Handle the case where there are more decimal places
            // than are desired.
            if ($currentDecimalLength >= $decimalPlaces) {                
                $roundedValue = number_format($number, $decimalPlaces, '.', '');
                
                if ($this->stringStartsWith((string) $roundedValue, '-') && +$roundedValue >= 0) {
                    $roundedValue = mb_substr($roundedValue, 1);
                }

                return $this->addPaddingToNumber($roundedValue, $decimalPlaces, $optionalDigits);
            }



            return $this->addPaddingToNumber($number, $decimalPlaces, $optionalDigits);

        } else {
            $padding = '';
            if ($decimalPlaces > 0) {
                $padding = '.' . str_repeat('0', $decimalPlaces - $optionalDigits);
            }

            return $number . $padding;
        }
    }

    /**
     * Converts a string to a float.
     *
     * Adapted from http://php.net/manual/en/function.floatval.php#114486
     *
     * @param $number
     *
     * @return float
     * @throws \Exception
     */
    protected function toFloat($number)
    {
        $dotPos = $this->coalesce(strpos($number, '.'), -1);
        $commaPos = $this->coalesce(strpos($number, ','), -1);
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            $value = floatval(preg_replace("/[^0-9]/", "", $number));
        } else {
            $value = floatval(
                preg_replace("/[^0-9]/", "", substr($number, 0, $sep)) . '.' .
                preg_replace("/[^0-9]/", "", substr($number, $sep + 1, strlen($number)))
            );
        }

        if ($this->isNegativeNumber($value)) {
            $value = -1 * $value;
        }

        return $value;
    }

    /**
     * Determines if a string contains a negative number.
     *
     * @param $number
     *
     * @return bool
     */
    protected function isNegativeNumber($number)
    {
        return ((count(explode('-', $number)) + min(
                    count(explode('(', $number)) - 1,
                    count(explode(')', $number)) - 1)
            ) % 2) ? false : true;
    }

    /**
     * Normalizes decimal symbols.
     *
     * @param $string
     *
     * @return mixed
     * @throws \Exception
     */
    protected function normalizeDecimalSymbol($string)
    {
        $cultureInformation = $this->languageManager->currentCulture()->getDelimiters();
        $abbreviations = $this->languageManager->currentCulture()->getAbbreviations();
        $currencyInformation = $this->languageManager->currentCulture()->getCurrency();

        if ($cultureInformation['decimal'] !== '.') {
            $string = str_replace('.', '', $string);
        }

        $string = strtr($string,
            [
                $currencyInformation['symbol'] => '',
                $abbreviations['thousand'] => '',
                $abbreviations['million'] => '',
                $abbreviations['billion'] => '',
                $abbreviations['trillion'] => '',
                $cultureInformation['thousands'] => '',
                $cultureInformation['decimal'] => '.'
            ]
        );
        return $string;
    }

    /**
     * Gets an abbreviation from the language settings.
     *
     * @param $key
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getAbbreviation($key)
    {
        return $this->languageManager->currentCulture()->getAbbreviations()[$key];
    }

    /**
     * Checks if the given value is null or false. Returns the value if not,
     * otherwise the default value is returned.
     *
     * @param $value
     * @param $default
     *
     * @return mixed
     */
    private function coalesce($value, $default)
    {
        if ($value === null || $value === false) {
            return $default;
        }

        return $value;
    }

    /**
     * Formats a number with the specified currency format.
     *
     * @param $number
     * @param $format
     *
     * @return array|null|string
     * @throws \Exception
     */
    public function formatCurrency($number, $format = '')
    {
        $newFormat = $format;
        $symbolIndex = $this->coalesce(strpos($newFormat, '$'), -1);
        $openParenIndex = $this->coalesce(strpos($newFormat, '('), -1);
        $plusSignIndex = $this->coalesce(strpos($newFormat, '+'), -1);
        $minusSignIndex = $this->coalesce(strpos($newFormat, '-'), -1);
        $space = '';
        $decimalSeparator = '';

        $currentLocaleCurrency = $this->languageManager->currentCulture()->getCurrency();

        $spliceIndex = null;
        $output = null;

        if (!$this->stringContains($newFormat, '$')) {
            if ($currentLocaleCurrency['position'] === 'infix') {
                $decimalSeparator = $currentLocaleCurrency['symbol'];
                if ($currentLocaleCurrency['spaceSeparated']) {
                    $decimalSeparator = ' ' . $decimalSeparator . ' ';
                }
            } else {
                if ($currentLocaleCurrency['spaceSeparated']) {
                    $space = ' ';
                }
            }
        } else {
            if ($this->stringContains($newFormat, ' $')) {
                $space = ' ';
                $newFormat = str_replace(' $', '', $newFormat);
            } elseif ($this->stringContains($newFormat, '$ ')) {
                $space = ' ';
                $newFormat = str_replace('$ ', '', $newFormat);
            } else {
                $newFormat = str_replace('$', '', $newFormat);
            }

        }
        $this->checkFormats[] = $newFormat;
        $output = $this->formatNumber($number, $newFormat, $decimalSeparator);

        if (!$this->stringContains($format, '$')) {
            switch ($currentLocaleCurrency['position']) {
                case 'postfix':
                    if ($this->stringContains($output, ')')) {
                        $output = str_split($output);
                        array_splice($output, -1, 0, $space . $currentLocaleCurrency['symbol']);
                        $output = implode('', $output);
                    } else {
                        $output = $output . $space . $currentLocaleCurrency['symbol'];
                    }
                    break;
                case 'infix':
                    break;
                case 'prefix':
                    if ($this->stringContains($output, ['(', '-'])) {
                        $output = str_split($output);
                        $spliceIndex = max($openParenIndex, $minusSignIndex) + 1;
                        array_splice($output, $spliceIndex, 0, $currentLocaleCurrency['symbol'] . $space);
                        $output = implode('', $output);
                    } else {
                        $output = $currentLocaleCurrency['symbol'] . $space . $output;
                    }
                    break;
                default:
                    throw new Exception('Currency position should be among["prefix", "infix", "postfix"]');
            }
        } else {
            if ($symbolIndex <= 1) {
                if ($this->stringContains($output, ['(', '+', '-'])) {
                    $output = str_split($output);
                    $spliceIndex = 1;
                    if ($symbolIndex < $openParenIndex || $symbolIndex < $plusSignIndex || $symbolIndex < $minusSignIndex) {
                        $spliceIndex = 0;
                    }
                    array_splice($output, $spliceIndex, 0, $currentLocaleCurrency['symbol'] . $space);
                    $output = implode('', $output);
                } else {
                    $output = $currentLocaleCurrency['symbol'] . $space . $output;
                }
            } else {
                if ($this->stringContains($output, ')')) {
                    $output = str_split($output);
                    array_splice($output, -1, 0, $space . $currentLocaleCurrency['symbol']);
                    $output = implode('', $output);
                } else {
                    $output = $output . $space . $currentLocaleCurrency['symbol'];
                }
            }
        }

        return $output;
    }

    /**
     * Formats a number with the specified percentage format.
     *
     * @param $number
     * @param $format
     *
     * @return array|null|string
     * @throws \Exception
     */
    public function formatPercentage($number, $format)
    {
        $space = '';
        $output = null;
        $value = $number * 100;

        // Check for space before %
        if ($this->stringContains($format, ' %')) {
            $space = ' ';
            $format = str_replace(' %', '', $format);
        } else {
            $format = str_replace('%', '', $format);
        }

        $output = $this->formatNumber($value, $format);

        if ($this->stringContains(')', $output)) {
            $output = explode('', $output);
            array_splice($output, -1, 0, $space . '%');
            $output = implode('', $output);
        } else {
            $output = $output . $space . '%';
        }

        return $output;
    }

    /**
     * Returns a string representing the given time-based number.
     *
     * For additional time formatting options, consider using the feature-rich
     * 'briannesbitt/Carbon' - https://github.com/briannesbitt/Carbon
     *
     * @param $number
     *
     * @return string
     */
    public function formatTime($number)
    {
        $hours = floor($number / 60 / 60);
        $minutes = floor(($number - ($hours * 60 * 60)) / 60);
        $seconds = round($number - ($hours * 60 * 60) - ($minutes * 60));

        return $hours . ':' .
        (($minutes < 10) ? '0' . $minutes : $minutes) . ':' .
        (($seconds < 10) ? '0' . $seconds : $seconds);
    }

    private $checkNumber = null;
    private $checkFormats = ['$(-0,0.00)'];

    /**
     * Forms a number according to the given format.
     *
     * @param        $number
     * @param        $format
     * @param string $sep
     *
     * @return string
     * @throws \Exception
     */
    public function formatNumber($number, $format, $sep = '')
    {
        $originalFormat = $format;

        $originalPHPFloatPrecision = ini_get('precision');
        $numberOfNumbers = strlen(preg_replace('/[^0-9]/', "", $number)) + 1;
        $floatPrecisionWasModified = false;

        if ($numberOfNumbers > $originalPHPFloatPrecision) {
            ini_set('precision', $numberOfNumbers);
            $floatPrecisionWasModified = true;
        }

        $negP = false;
        $signed = false;
        $optDec = false;
        $abbr = '';
        $i = null;
        $bytes = '';
        $ord = '';
        $abs = abs($number);
        $min = null;
        $max = null;
        $power = null;
        $totalLength = null;
        $length = null;
        $minimumPrecision = null;
        $pow = null;
        $w = null;
        $intPrecision = null;
        $precision = null;
        $prefix = null;
        $postfix = null;
        $thousands = null;
        $d = '';
        $forcedNeg = false;
        $neg = false;
        $indexOpenP = null;
        $size = null;
        $indexMinus = null;
        $minLen = null;

        // Check if number is zero and a custom zero format has been set.
        if ($this->isZero($number) && $this->zeroFormat != null) {
            return $this->zeroFormat;
        }

        if (is_infinite($number)) {
            return '' . $number;
        }

        if ($this->stringStartsWith($format, '{')) {
            $end = strpos($format, '}');
            if ($end === false) {
                throw new Exception('Format should also contain a "}"');
            }
            $prefix = $this->coalesce(substr($format, 1, $end - 1), -1);
            $format = $this->coalesce(substr($format, $end + 1), -1);
        } else {
            $prefix = '';
        }

        if ($this->stringEndsWith($format, '}')) {
            if (!$this->stringContains($format, '{')) {
                throw new Exception('Format should also contain a "{"');
            }
            $start = $this->coalesce(strpos($format, '{'), -1);
            $postfix = $this->coalesce(substr($format, $start + 1, -1), -1);
            $format = $this->coalesce(substr($format, 0, $start + 1), -1);
        } else {
            $postfix = '';
        }

        // Check for min length.
        $info = null;
        if (!$this->stringContains($format, '.')) {
            preg_match('/([0-9]+).*/', $format, $info);
        } else {
            preg_match('/([0-9]+)\..*/', $format, $info);
        }

        if ($info !== null && is_array($info) && isset($info[1])) {
            $minLen = strlen($info[1]);
        } else {
            $minLen = -1;
        }

        // Parentheses
        if (strpos($format, '-') !== false) {
            $forcedNeg = true;
        }

        if (strpos($format, '(') !== false) {
            $negP = true;
            $format = $this->coalesce(substr($format, 1, -1), -1);
        } elseif (strpos($format, '+') !== false) {
            $signed = true;
            $format = str_replace('+', '', $format);
        }

        // Abbreviations
        if ($this->stringContains($format, ['a', 'A'])) {

            $tempPrecisionSplit = explode('.', $format);
            if (isset($tempPrecisionSplit[0])) {
                $intPrecision = preg_replace('/[^0-9]/', "", $tempPrecisionSplit[0]);
            } else {
                $intPrecision = 0;
            }

            $abbrK = $this->stringContains($format, ['aK', 'AK']);
            $abbrM = $this->stringContains($format, ['aM', 'AM']);
            $abbrB = $this->stringContains($format, ['aB', 'AB']);
            $abbrT = $this->stringContains($format, ['aT', 'AT']);
            $abbrForce = $abbrK || $abbrM || $abbrB || $abbrT;

            // Check for space before abbreviation.
            if ($this->stringContains($format, [' a',' A'])) {
                $abbr = ' ';
                $format = str_replace(' a', '', $format);
            } else {
                $format = str_replace('a', '', $format);
            }


            $totalLength = floor(log($abs) / log(10)) + 1;
            $minimumPrecision = (int)$totalLength % 3;
            $minimumPrecision = ($minimumPrecision === 0) ? 3 : $minimumPrecision;

            if ($intPrecision > 0 && $abs !== 0) {
                $length = floor(log($abs) / log(10)) + 1 - $intPrecision;
                $pow = 3 * intval((min($intPrecision, $totalLength) - $minimumPrecision) / 3);
                $abs = $abs / pow(10, $pow);
                if (strpos($format, '.') === false && $intPrecision > 3) {
                    $format .= '[.]';
                    $size = $length === 0 ? 0 : 3 * intval($length / 3) - $length;
                    $size = $size < 0 ? $size + 3 : $size;
                    $format .= str_repeat('0', $size);
                }
            }

            if (floor(log(abs($number)) / log(10)) + 1 !== $intPrecision) {
                if ($abs >= pow(10, 12) && !$abbrForce || $abbrT) {
                    // Trillion
                    $abbr = $abbr . $this->getAbbreviation('trillion');
                    $number = $number / pow(10, 12);
                } elseif ($abs < pow(10, 12) && $abs >= pow(10, 9) && !$abbrForce || $abbrB) {
                    // Billion
                    $abbr = $abbr . $this->getAbbreviation('billion');
                    $number = $number / pow(10, 9);
                } elseif ($abs < pow(10, 9) && $abs >= pow(10, 6) && !$abbrForce || $abbrM) {
                    // Million
                    $abbr = $abbr . $this->getAbbreviation('million');
                    $number = $number / pow(10, 6);
                } elseif ($abs < pow(10, 6) && $abs >= pow(10, 3) && !$abbrForce || $abbrK) {
                    // Thousand
                    $abbr = $abbr . $this->getAbbreviation('thousand');
                    $number = $number / pow(10, 3);
                }
            }

            // If the format contains the letter A, and it is an upper case A, we will
            // go ahead and simply convert the abbreviation to upper case.
            if ($this->stringContains($originalFormat, 'A')) {
                $abbr = mb_strtoupper($abbr);
            }

        }

        // binary bytes
        if ($this->stringContains($format, ['b','B'])) {
            // check for spaces
            if ($this->stringContains($format, [' b', ' B'])) {
                $bytes = ' ';
                $format = strtr($format,[' b' => '', ' B' => '']);
            } else {
                $format = strtr($format,['b' => '', 'B' => '']);
            }

            for ($power = 0; $power <= count($this->formatBinarySuffixes); $power++) {
                $min = pow(1024, $power);
                $max = pow(1024, $power + 1);

                if ($number >= $min && $number < $max) {
                    $bytes = $bytes . $this->formatBinarySuffixes[$power];
                    if ($min > 0) {
                        $number = $number / $min;
                    }
                    break;
                }
            }

            if ($number == 0 && $bytes === '' || $bytes === ' ') {
                $bytes = $bytes.'b';
            }

            if ($this->stringContains($originalFormat, 'B')) {
                $bytes = mb_strtoupper($bytes);
            }
        }

        // decimal bytes
        if ($this->stringContains($format, ['d','D'])) {
            // check for spaces
            if ($this->stringContains($format, [' d', ' D'])) {
                $bytes = ' ';
                $format = strtr($format, [' d' => '', ' D' => '']);
            } else {
                $format = strtr($format, ['d' => '', 'D' => '']);
            }

            for ($power = 0; $power <= count($this->formatDecimalSuffixes); $power++) {
                $min = pow(1000, $power);
                $max = pow(1000, $power + 1);
                if ($number >= $min && $number < $max) {
                    $bytes = $bytes . $this->formatDecimalSuffixes[$power];
                    if ($min > 0) {
                        $number = $number / $min;
                    }
                    break;
                }
            }

            if ($this->stringContains($originalFormat, 'D')) {
                $bytes = mb_strtoupper($bytes);
            }

        }

        // Ordinal
        if ($this->stringContains($format, 'o')) {
            // Check for space.
            if ($this->stringContains($format, ' o')) {
                $ord = ' ';
                $format = str_replace(' o', '', $format);
            } else {
                $format = str_replace('o', '', $format);
            }

            $ordinalTemp = $this->languageManager->currentCulture()->ordinal($number);
            if ($ordinalTemp !== null) {
                $ord = $ord . $ordinalTemp;
                unset($ordinalTemp);
            }
        }

        if (strpos($format, '[.]') !== false) {
            $optDec = true;
            $format = str_replace('[.]', '.', $format);
        }

        $tempWValue = explode('.', $number);
        $tempPrecValue = explode('.', $format);
        $thousands = (strpos($format, ',') !== false);

        if (count($tempWValue) > 0) {
            $w = $tempWValue[0];
        } else {
            $w = null;
        }
        if (isset($tempPrecValue[1])) {
            $precision = $tempPrecValue[1];
        } else {
            $precision = null;
        }

        if ($precision !== null && $precision >= 0) {
            if (strpos($precision, '*') !== false) {
                $pTempD = explode('.', $number);

                if (count($pTempD) > 0 && isset($pTempD[1])) {
                    $pTempD = strlen($pTempD[1]);
                } else {
                    $pTempD = 0;
                }
                $d = $this->toFixed($number, $pTempD);
            } else {
                if ($this->stringContains($precision, '[')) {
                    $precision = str_replace(']', '', $precision);
                    $precision = explode('[', $precision);
                    $precisionLength = strlen($precision[0]) + strlen($precision[1]);
                    $optionalLength = strlen($precision[1]);
                    $d = $this->toFixed($number, $precisionLength, $optionalLength);
                } else {
                    $d = $this->toFixed($number, strlen($precision));
                }
            }

            $iTempWValue = explode('.', $d);
            if (count($iTempWValue) > 0) {
                $w = $iTempWValue[0];
            } else {
                $w = null;
            }

            if (isset($iTempWValue[1]) && strlen($iTempWValue[1]) > 0) {
                $p = $sep ? $abbr . $sep : $this->languageManager->currentCulture()->getDelimiters()['decimal'];
                $tempDSplit = explode('.', $d);
                $d = $p . $tempDSplit[1];
            } else {
                $d = '';
            }

            if ($optDec && intval($this->toFloat(substr($d, 1))) === 0) {
                $d = '';
            }

        } else {
            $w = $this->toFixed($number, 0);
        }

        if (strpos($w, '-') !== false) {
            $w = $this->coalesce(substr($w, 1), -1);
            $neg = true;
        }

        if (strlen($w) < $minLen) {
            $w = str_repeat('0', $minLen - strlen($w)) . $w;
        }

        if ($thousands) {
            $w = preg_replace('/(\d{1,3})(?=(\d{3})+$)/',
                '\\1' . $this->languageManager->currentCulture()->getDelimiters()['thousands'],
                $w);
        }

        if (strpos($format, '.') === 0) {
            $w = '';
        }

        $indexOpenP = $this->coalesce(strpos($format, '('), -1);
        $indexMinus = $this->coalesce(strpos($format, '-'), -1);

        if ($indexOpenP < $indexMinus) {
            $paren = (($negP && $neg) ? '(' : '') . ((($forcedNeg && $neg)
                    || (!$negP && $neg)) ? '-' : '');
        } else {
            $paren = ((($forcedNeg && $neg) || (!$negP && $neg)) ? '-' : '') .
                (($negP && $neg) ? '(' : '');
        }

        if ($floatPrecisionWasModified) {
            ini_set('precision', $originalPHPFloatPrecision);
        }

        $result = $prefix . $paren . ((!$neg && $signed && $number !== 0) ? '+' : '') .
            $w . $d . (($ord) ? $ord : '') .
            (($abbr && !$sep) ? $abbr : '') .
            (($bytes) ? $bytes : '') .
            (($negP && $neg) ? ')' : '') .
            $postfix;

        return $result;
    }

    /**
     * Formats a number according to the provided format.
     *
     * If the format contains a '$' symbol, the number will be formatted as currency.
     * If the format contains a '%' symbol, the number will be formatted as a percentage.
     * If the format contains a ':' symbol, the number will be formatted as time.
     * If the format does not contain any of the previous symbols, it will be formatted
     * as a number according to the format.
     *
     * @param        $number
     * @param string $format
     *
     * @return array|null|string
     * @throws \Exception
     */
    public function format($number, $format = '')
    {
        $output = null;

        $escapedFormat = preg_replace('/\{[^\{\}]*\}/', '', $format);

        if ($this->stringContains($escapedFormat, '$')) {
            // Format the number as currency.
            $output = $this->formatCurrency($number, $format);
        } elseif ($this->stringContains($escapedFormat, '%')) {
            // Format the number as a percentage.
            $output = $this->formatPercentage($number, $format);
        }  elseif ($this->stringContains($escapedFormat, ':')) {
            // Format the number as time.
            $output = $this->formatTime($number);
        } else {
            // Format as number or bytes.
            $output = $this->formatNumber($number, $format);
        }

        return $output;
    }

    /**
     * Unformats time.
     *
     * @param $string
     *
     * @return float
     */
    public function unformatTime($string)
    {
        $timeArray = explode(':', $string);
        $seconds = 0;
        if (count($timeArray) === 3) {
            // Hours
            $seconds = $seconds + ($this->toFloat($timeArray[0])) * 60 * 60;
            // Minutes
            $seconds = $seconds + ($this->toFloat($timeArray[1])) * 60;
            // Seconds
            $seconds = $seconds + ($this->toFloat($timeArray[2]));
        } else {
            if (count($timeArray) == 2) {
                // Minutes
                $seconds = $seconds + ($this->toFloat($timeArray[0])) * 60;
                // Seconds
                $seconds = $seconds + ($this->toFloat($timeArray[1]));
            }
        }

        return floatval($seconds);
    }

    /**
     * Converts a string-based number into a usable number.
     *
     * @param $string
     *
     * @return float|int|null|number
     */
    public function unformat($string)
    {
        $originalString = $this->decode($string);
        $string = $this->decode($string);

        if (is_numeric($string)) {
            return $string;
        }

        if ($this->stringContains($string, ':')) {
            return $this->unformatTime($string);
        } else {
            if ($this->isZero($string)) {
                return 0;
            } else {
                $string = $this->normalizeDecimalSymbol($string);
                $bytesMultiplier = null;

                $abbreviations = $this->languageManager->currentCulture()->getAbbreviations();
                $currencySymbol = $this->languageManager->currentCulture()->getCurrency()['symbol'];

                // This seems to get rid of an error that appears. The error message is
                // \k is not followed by a braced, angle-bracketed, or quoted name at offset x
                $regexCurrencySymbol = implode('\\', str_split($currencySymbol));
                if ($this->stringStartsWith($regexCurrencySymbol, 'k')) {
                    $regexCurrencySymbol = '\\' . $regexCurrencySymbol;
                }


                for ($power = 0; $power < count($this->binarySuffixes) && !$bytesMultiplier; $power++) {
                    if ($this->stringContains($string, $this->binarySuffixes[$power])) {
                        $bytesMultiplier = pow(1024, $power + 1);
                    } else {
                        if ($this->stringContains($string, $this->suffixes[$power])) {
                            $bytesMultiplier = pow(1000, $power + 1);
                        }
                    }
                }

                // Remove currency symbol from the original format.
                $originalString = strtr($originalString, [$currencySymbol => '']);

                $value = (($bytesMultiplier) ? $bytesMultiplier : 1);

                // We want to sort the abbreviations by most specific first and then
                // do the abbreviations check. For a language use-case, see the
                // unformat tests for pt-BR.
                uasort($abbreviations, function ($a, $b) {
                    return mb_strlen($b) - mb_strlen($a);
                });

                foreach ($abbreviations as $name => $abbreviation) {
                    $regex = '/[^a-zA-Z]' . $abbreviation . '(?:\)|(\\' .
                        $regexCurrencySymbol . ')?(?:\))?)?/';
                    if (preg_match($regex, $originalString)) {
                        switch ($name) {
                            case 'thousand':
                                $value *= pow(10, 3);
                                break;
                            case 'million':
                                $value *= pow(10, 6);
                                break;
                            case 'billion':
                                $value *= pow(10, 9);
                                break;
                            case 'trillion':
                                $value *= pow(10, 12);
                                break;
                        }
                        break;
                    }
                }

                $value *= (($this->stringContains($originalString,
                        '%')) ? 0.01 : 1) * ($this->isNegativeNumber($string) ? -1 : 1) * $this->toFloat(preg_replace('/[^0-9\.]/',
                        '', $string));

                $value = ($bytesMultiplier) ? ceil($value) : $value;

                return $value;
            }
        }
    }

}
