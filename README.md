![numeral.php](https://cloud.githubusercontent.com/assets/5232890/18261204/ba157f90-73ba-11e6-96d5-037841cee49c.png)

[![Build Status](https://travis-ci.org/Stillat/numeral.php.svg?branch=master)](https://travis-ci.org/Stillat/numeral.php)
[![Latest Stable Version](https://poser.pugx.org/stillat/numeral.php/v/stable)](https://packagist.org/packages/stillat/numeral.php) [![Total Downloads](https://poser.pugx.org/stillat/numeral.php/downloads)](https://packagist.org/packages/stillat/numeral.php) [![Latest Unstable Version](https://poser.pugx.org/stillat/numeral.php/v/unstable)](https://packagist.org/packages/stillat/numeral.php) [![License](https://poser.pugx.org/stillat/numeral.php/license)](https://packagist.org/packages/stillat/numeral.php)

Numeral.php is a number formatting library for PHP. It started off as a port of [`numerals-js`](http://numeraljs.com/) (a number formatting library for JavaScript). This is where Numeral.php gets it's name. The latest iteration of Numeral.php is based on [`numbro`](https://github.com/foretagsplatsen/numbro), a maintained "version" of numerals-js. The API is very similar to `numbro`, but there are some things that are different. Most of the documentation here is taken from the [`numbro.js website`](http://numbrojs.com/format.html), and adjusted where necessary, so be sure to give them some love.

Here is a quick example:

```php
// +10,000
$formatter->format(1000.23, '+0,0');

// 233.43k
$formatter->format(233434, '0a.00');
```

## Installation

This package can be installed using Composer by issuing this command in the root of your project:

```
composer require stillat/numeral.php
```

## Getting Started

Working with Numeral.php is fairly straightforward. There are two things you have to do when first getting started. First, we need to create a language manager instance, and then a `Numeral` (this class does all the formatting and unformatting) instance. The language manager handles loading culture information that the formatter will use.

Doing this might look like this:

```php
<?php

use Stillat\Numeral\Languages\LanguageManager;
use Stillat\Numeral\Numeral;

// Create the language manager instance.
$languageManager = new LanguageManager;

// Create the Numeral instance.
$formatter = new Numeral;

// Now we need to tell our formatter about the language manager.
$formatter->setLanguageManager($languageManager);
```

At this point we can use our `$formatter` and do all the cool and fun things with it.

## Formatting

Numbers can be formatted to look like currency, percentages, times, or even plain old numbers with decimal places, thousands, and abbreviations.

```php
// 1,000
$string = $formatter->format(1000, '0,0');
```

### Numbers

| Number | Format | String |
|---|---|---|
| 10000 | '0,0.0000' | 10,000.0000 |
| 10000.23 | '0,0' | 10,000 |
| 10000.23 | '+0,0' | +10,000 |
| -10000 | '0,0.0' | -10,000.0 |
| 10000.1234 | '0.000' | 10000.123 |
| 10000.1234 | '0[.]00000' | 10000.12340 |
| -10000 | '(0,0.0000)' | (10,000.0000) |
| -0.23 | '.00' | -.23 |
| -0.23 | '(.00)' | (.23) |
| 0.23 | '0.00000' | 0.23000 |
| 0.23 | '0.0[0000]' | 0.23 |
| 1230974 | '0.0a' | 1.2m |
| 1460 | '0 a' | 1 k |
| -104000 | '0a' | -104k |
| 233434 | '0a.00' | 233.43k |
| 233000 | '0a.00' | 233.00k |
| 1 | '0o' | 1st |
| 52 | '0o' | 52nd |
| 23 | '0o' | 23rd |
| 100 | '0o' | 100th |

### Average

Numeral.php provides an easy mechanism to round up any number with the special formatting character `a`. Note that the delimiters are per language.

| Number | Format | String |
|---|---|---|
| 123 | '0a' | 123 |
| 1234 | '0a' | 1k |
| 12345 | '0a' | 12k |
| 123456 | '0a' | 123k |
| 1234567 | '0a' | 1m |
| 12345678 | '0a' | 12m |
| 123456789 | '0a' | 123m |

> **Uppercase Variant**: You can supply an uppercase variant of this by using the `A` special formatting character. This is *not* present in `numbro.js`.

In addition to this, when numbers are rounded up, one can specify the precision wanted with a format like `3a` (for 3 numbers only). In this case, `0` is used for "automatic" mode.

| Number | Format | String |
|---|---|---|
| 1234567891 | '0a' | 1b |
| 1234567891 | '1a' | 1b |
| 1234567891 | '2a' | 1b |
| 1234567891 | '3a' | 1b |
| 1234567891 | '4a' | 1235m |
| 1234567891 | '5a' | 1234.6m |
| 1234567891 | '6a' | 1234.57m |
| 1234567891 | '7a' | 1234568k |
| 1234567891 | '8a' | 1234567.9k |
| 1234567891 | '9a' | 1234567.89k |
| 1234567891 | '10a' | 1234567891 |

### Currency

Numeral.php supports cultural currency formatting via the function `formatCurrency`.

```php
// $1000
$formatter->formatCurrency(1000.234);
```

| Number | Language | String |
|---|---|---|
| 1000.234 | en-US | $0,001 k |
| 1000.234 | fr-FR | 1 000 € |
| 1000.234 | ja-JP | ¥1,000 |
| 1000.234 | ru-RU | 1 000 руб. |

You can provide a more specific format, as long as you do not use the `$` character:

```php
// $1000.23
$formatter->formatCurrency(1000.234, '0[.]00');
```

| Number | Language | String |
|---|---|---|
| 1000.234 | en-US | $1000.23 |
| 1000.234 | fr-FR | 1000,23€ |
| 1000.234 | ja-JP | ¥1000.23 |
| 1000.234 | ru-RU | 1000,23руб. |

But if you want to, you can always a provide a format by hand to override the languages defaults:

| Number | Format | String |
|---|---|---|
| 1000.234 | '$0,0.00' | $1,000.23 |
| 1000.2 | '0,0[.]00 $' | 1,000.20 $ |
| 1001 | '$ 0,0[.]00' | $ 1,001 |
| -1000.234 | '($0,0)' | ($1,000) |
| -1000.234 | '$0.00' | -$1000.23 |
| 1230974 | '($ 0.00 a)' | $ 1.23 m |

### Bytes

| Number | Format | String |
|---|---|---|
| 100 | '0b' | 100B |
| 2048 | '0 b' | 2 KiB |
| 7884486213 | '0.0b' | 7.3GiB |
| 3467479682787 | '0.000 b' | 3.154 TiB |

### Percentages

| Number | Format | String |
|---|---|---|
| 1 | '0%' | 100% |
| 0.974878234 | '0.000%' | 97.488% |
| -0.43 | '0 %' | -43 % |
| 0.43 | '(0.000 %)' | 43.000 % |

### Time

| Number | Format | String |
|---|---|---|
| 25 | '00:00:00' | 0:00:25 |
| 238 | '00:00:00' | 0:03:58 |
| 63846 | '00:00:00' | 17:44:06 |

If you want to format time more than that, you should check out the [`Carbon`](https://github.com/briannesbitt/carbon) library.

## Unformat

You can unformat a string using the `unformat` function.

```php
// -10000
$number = $formatter->unformat('($10,000.00)');
```

| String | Function | Number |
|---|---|---|
| '10,000.123' | `unformat("10,000.123")` | 10000.123 |
| '0.12345' | `unformat('0.12345')` | 0.12345 |
| '1.23m' | `unformat('1.23m')` | 1230000 |
| '23rd' | `unformat('23rd')` | 23 |
| '$10,000.00' | `unformat('$10,000.00')` | 10000 |
| '100B' | `unformat('100B')` | 100 |
| '3.154TB' | `unformat('3.154TB')` | 3154000000000 |
| '-76%' | `unformat('-76%')` | -0.76 |
| '2:23:57' | `unformat('2:23:57')` | 8637 |

## Default Zero

You can set a custom output when formatting numbers with a value of `0`:

```php
$formatter->setZeroFormat('N/A');

// 'N/A'
$number = $formatter->format('0');
```
## Acknowledgements

Numeral.php is based on [numbro](https://github.com/foretagsplatsen/numbro), which is itself a fork of [Adam Draper's](https://github.com/adamwdraper) project [Numerals.js](http://numeraljs.com/) (the name of Numeral.php was influenced by this project). Numbro made many improvements to numerals, so the codebase of Numeral.php is heavily influenced by it (Numbro formats should work without much, if any, modification in Numeral.php).

## License

Copyright © 2014 Adam Draper

Copyright © 2015 Företagsplatsen AB

Copyright © 2016 Johnathon Koster

---

Distributed under the MIT license. If you want to know more, see the `LICENSE.txt` file.

The original license for `Numbro` can be found in the `LICENSE-numbro`

The original license file for `Numeral.js` can be found in `LICENSE-Numeraljs`
