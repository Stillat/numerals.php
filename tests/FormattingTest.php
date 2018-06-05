<?php

use Stillat\Numeral\Languages\LanguageManager;
use Stillat\Numeral\Numeral;

class FormattingTest extends PHPUnit_Framework_TestCase
{

    protected $basicCurrencyTests = [
        [1000.234, '$0,0.00', '$1,000.23'],
        [1001, '$ 0,0.[00]', '$ 1,001'],
        [1000.234, '+$0,0.00', '+$1,000.23'],
        [1000.234, '$+0,0.00', '$+1,000.23'],
        [1000.234, '0,0.00 $', '1,000.23 $'],
        [-1000.234, '($0,0)', '($1,000)'],
        [-1000.234, '(0,0$)', '(1,000$)'],
        [-1000.234, '$0.00', '-$1000.23'],
        [-1000.234, '+$0,0.00', '-$1,000.23'],
        [1230974, '($0.00 a)', '$1.23 m'],
        [1000.234, '+$0a', '+$1k'],

        // test symbol position before negative sign / open parens
        [-1000.234, '$ (0,0)', '$ (1,000)'],
        [-1000.234, '$(0,0)', '$(1,000)'],
        [-1000.234, '$ (0,0.00)', '$ (1,000.23)'],
        [-1000.234, '$(0,0.00)', '$(1,000.23)'],
        // [-1000.238, '$(0,0.00)', '$(1,000.24)'],
        [-1000.238, '$(-0,0.00)', '$(-1,000.24)'],
        [-1000.234, '$-0,0', '$-1,000'],
        [-1000.234, '$ -0,0', '$ -1,000'],

        [1000.234, '$ (0,0)', '$ 1,000'],
        [1000.234, '$(0,0)', '$1,000'],
        [1000.234, '$ (0,0.00)', '$ 1,000.23'],
        [1000.234, '$(0,0.00)', '$1,000.23'],
        [1000.238, '$(0,0.00)', '$1,000.24'],
        [1000.234, '$-0,0)', '$1,000'],
        [1000.234, '$ -0,0', '$ 1,000'],
    ];

    protected $numbers = [
        [0, '+0', '0'],
        [10000, '0,0.0000', '10,000.0000'],
        [-10000, '0,0.0', '-10,000.0'],
        [10000.23, '0,0', '10,000'],
        [10000.1234, '0.000', '10000.123'],
        [-10000, '(0,0.0000)', '(10,000.0000)'],
        [10000, '0,0.0000', '10,000.0000'],
        [10000, '0[.]00', '10000'],
        [10000.1, '0[.]00', '10000.10'],
        [10000.123, '0[.]00', '10000.12'],
        [10000.456, '0[.]00', '10000.46'],
        [10000.001, '0[.]00', '10000'],
        [10000.45, '0[.]00[0]', '10000.45'],
        [10000.456, '0[.]00[0]', '10000.456'],
        [-12300, '+0,0.0000', '-12,300.0000'],
        [1230, '+0,0', '+1,230'],
        [100.78, '0', '101'],
        [100.28, '0', '100'],
        [1.932, '0.0', '1.9'],
        [1.9687, '0', '2'],
        [1.9687, '0.0', '2.0'],
        [-0.23, '.00', '-.23'],
        [-0.23, '(.00)', '(.23)'],
        [0.23, '0.00000', '0.23000'],
        [0.67, '0.0[0000]', '0.67'],
        [1.005, '0.00', '1.01'],
        [2000000000, '0.0a', '2.0b'],
        [1230974, '0.0a', '1.2m'],
        [1460, '0a', '1k'],
        [-104000, '0 a', '-104 k'],
        [1, '0o', '1st'],
        [52, '0 o', '52 nd'],
        [23, '0o', '23rd'],
        [100, '0o', '100th'],
        [3124213.12341234, '0.*', '3124213.12341234'],
        [3124213.12341234, ',0.*', '3,124,213.12341234'],
        [0.23, '0.0[0000]', '0.23'],
        [1230974, '0.0a', '1.2m'],
        [1460, '0 a', '1 k'],
        [-104000, '0a', '-104k'],
        [233434, '0a.00', '233.43k'],
        [233000, '0a.00', '233.00k'],
        [10000.23, '+0,0', '+10,000'],
        [10000.23, '0,0', '10,000'],
        [-10000, '0,0.0', '-10,000.0'],
        [-0.23, '.00', '-.23'],
        [0.23, '0.0[0000]', '0.23'],
        [10000.1234, '0[.]00000', '10000.12340'],
        [1.10, '0000.00', '0001.10'],
        [2011.10, '0000.00', '2011.10'],
        [2011.10, '000000.00', '002011.10'],
        [100, '0[.]000', '100'],
        [100.12, '0[.]000', '100.120'],
        [100.12, '0[.]00[0]', '100.12'],
        [100.12, '0[.]00[000]', '100.12'],
        [100.123, '0[.]00[000]', '100.123'],
        [100.1234, '0[.]00[000]', '100.1234'],
        [100.12345, '0[.]00[000]', '100.12345'],
        [100.123456, '0[.]00[000]', '100.12346'],
        [100.123453, '0[.]00[000]', '100.12345'],
        [0.069999999999993, '0[.]00', '0.07'],


        // specified abbreviations
        [-5444333222111, '0,0 aK', '-5,444,333,222 k'],
        [-5444333222111, '0,0 aM', '-5,444,333 m'],
        [-5444333222111, '0,0 aB', '-5,444 b'],
        [-5444333222111, '0,0 aT', '-5 t'],

        // small numbers
        [1e-5, '0','0'],
        [1e-5, '0.0','0.0'],
        [1e-5, '0.00000','0.00001'],
        [1e-5, '0.00000000','0.00001000'],
        [1e-23, '0.0','0.0'],
        [1e-23, '0.000','0.000'],
        [1e-23, '0.00000000000000000000000','0.00000000000000000000001'],
        [1.1e-23, '0.000000000000000000000000','0.000000000000000000000011'],
        [-0.001, '0.00','0.00'],
        [-0.001, '0.000','-0.001'],
        [-1e-5, '0.00','0.00'],
        [-1e-23, '0.0000000000000000000000','0.0000000000000000000000'],
        [-1e-23, '0.00000000000000000000000','-0.00000000000000000000001'],
        [-1.1e-23, '0.000000000000000000000000','-0.000000000000000000000011'],
    ];

    protected $averageTests = [
        [123, '123'],
        [1234, '1k'],
        [12345, '12k'],
        [123456, '123k'],
        [1234567, '1m'],
        [12345678, '12m'],
        [123456789, '123m'],
    ];

    protected $averageTestsDifferentModes = [
        ['0a', '1b'],
        ['1a', '1b'],
        ['2a', '1b'],
        ['3a', '1b'],
        ['4a', '1235m'],
        ['5a', '1234.6m'],
        ['6a', '1234.57m'],
        ['7a', '1234568k'],
        ['8a', '1234567.9k'],
        ['9a', '1234567.89k'],
        ['10a', '1234567891'],
    ];

    protected $timeTests = [
        [25, '00:00:00', '0:00:25'],
        [238, '00:00:00', '0:03:58'],
        [63846, '00:00:00', '17:44:06']
    ];

    protected $bytesTest = [
        [0, '0 b', '0 b'],
        [0, '0 B', '0 B'],
        [0, '0B', '0B'],
        [0, '0b', '0b'],
        [100, '0b', '100B'],
        [2048, '0 b', '2 KiB'],
        [7884486213, '0.0b', '7.3GiB'],
        [3467479682787, '0.000 b', '3.154 TiB'],

        [100, '0b', '100B'],
        [2048, '0 b', '2 KiB'],
        [5242880, '0b', '5MiB'],
        [7884486213.632, '0.[0] b', '7.3 GiB'],
        [3.4674797e+12, '0.000b', '3.154TiB'],
        [3.3252942e+15, '0b', '3PiB'],
    ];

    protected $decimalBytesTest = [
        [100, '0d', '100B'],
        [1000, '0d', '1KB'],
        [10000, '0d', '10KB'],
        [100000, '0d', '100KB'],
        [1000000, '0d', '1MB'],
        [10000000, '0d', '10MB'],
        [100000000, '0d', '100MB'],
        [1000000000, '0d', '1GB'],
        [10000000000, '0d', '10GB'],
        [100000000000, '0d', '100GB'],
        [1000000000000, '0d', '1TB'],
        [10000000000000, '0d', '10TB'],
        [100000000000000, '0d', '100TB'],
        [1000000000000000, '0d', '1PB'],
        [10000000000000000, '0d', '10PB'],
        [100000000000000000, '0d', '100PB'],
        [1000000000000000000, '0d', '1EB'],
        [10000000000000000000, '0d', '10EB'],
        [100000000000000000000, '0d', '100EB'],
        [1000000000000000000000, '0d', '1ZB'],
        [10000000000000000000000, '0d', '10ZB'],
        [100000000000000000000000, '0d', '100ZB'],
        [1000000000000000000000000, '0d', '1YB'],
        [10000000000000000000000000, '0d', '10YB'],
        [100000000000000000000000000, '0d', '100YB'],
    ];

    protected $percentageTests = [
        [1, '0%', '100%'],
        [0.974878234, '0.000%', '97.488%'],
        [-0.43, '0 %', '-43 %'],
        [0.43, '(0.00[0]%)', '43.00%']
    ];

    protected $verbatimTests = [
        [100, '{foo }0o', 'foo 100th'],
        [100, '0o{ foo}', '100th foo'],
    ];

    /**
     * The LanguageManager instance.
     *
     * @var \Stillat\Numeral\Languages\LanguageManager
     */
    protected $languageManager;

    /**
     * The Numeral instance.
     *
     * @var \Stillat\Numeral\Numeral
     */
    protected $numeral;

    public function setUp()
    {
        $this->languageManager = new LanguageManager;
        $this->numeral = new Numeral;
        $this->numeral->setLanguageManager($this->languageManager);
    }

    protected function assertActuallyEquals($expected, $value)
    {
        $this->assertEquals(strlen($expected), strlen($value), "Expected {$expected} got {$value}");
        $this->assertEquals($expected, $value);
    }

    protected function runTestsOnArray($array, $func)
    {
        foreach ($array as $test) {
            $value = $this->numeral->{$func}($test[0], $test[1]);
            $this->assertActuallyEquals($test[2], $value);
        }
    }

    /**
     * @expectedException \Exception
     */
    public function testFormatThrowsExceptionOnMissingClosingBracket()
    {
        $this->numeral->formatNumber(230, '{foo 0o');
    }

    /**
     * @expectedException \Exception
     */
    public function testFormatThrowsExceptionOnMissingOpeningBracket()
    {
        $this->numeral->formatNumber(230, '0o foo}');
    }

    public function testFormatCurrency()
    {
        $this->runTestsOnArray($this->basicCurrencyTests, 'formatCurrency');
    }

    public function testFormatNumbers()
    {
        $this->runTestsOnArray($this->numbers, 'formatNumber');
        $this->runTestsOnArray($this->numbers, 'format');
    }

    public function testFormatBytes()
    {
        $this->runTestsOnArray($this->bytesTest, 'formatNumber');
        $this->runTestsOnArray($this->bytesTest, 'format');
    }

    public function testFormatDecimalBytes()
    {
        $this->runTestsOnArray($this->decimalBytesTest, 'formatNumber');
        $this->runTestsOnArray($this->decimalBytesTest, 'format');
    }

    public function testAutomaticAverageMode()
    {
        foreach ($this->averageTests as $test) {
            $value = $this->numeral->formatNumber($test[0], '0a');
            $this->assertEquals($test[1], $value);
        }
    }

    public function testDifferentAverageModes()
    {
        foreach ($this->averageTestsDifferentModes as $test) {
            $value = $this->numeral->formatNumber(1234567891, $test[0]);
            $this->assertEquals($test[1], $value);
        }
    }

    public function testAbbreviationsCanBeUppercase()
    {
        $tests = [
            [1460, '0a', '1k'],
            [1460, '0A', '1K'],
            [2460, '0a', '2k'],
            [-5444333222111, '0,0 aT', '-5 t'],
            [-5444333222111, '0,0 AT', '-5 T'],
        ];

        $this->runTestsOnArray($tests, 'formatNumber');
        $this->runTestsOnArray($tests, 'format');
    }

    public function testDecimalsCanBeUppercase()
    {
        $tests = [
            [10000000, '0d', '10MB'],
            [100000000, '0d', '100MB'],
            [10000000, '0D', '10MB'],
            [100000000, '0D', '100MB'],
        ];

        $this->runTestsOnArray($tests, 'formatNumber');
        $this->runTestsOnArray($tests, 'format');
    }

    public function testBytesCanBeUppercase()
    {
        $tests = [
            [100, '0b', '100B'],
            [2048, '0 b', '2 KiB'],
            [7884486213, '0.0b', '7.3GiB'],
            [100, '0B', '100B'],
            [2048, '0 B', '2 KIB'],
            [7884486213, '0.0B', '7.3GIB'],
        ];

        $this->runTestsOnArray($tests, 'formatNumber');
        $this->runTestsOnArray($tests, 'format');
    }

    public function testVerbatimText()
    {
        $this->runTestsOnArray($this->verbatimTests, 'formatNumber');
        $this->runTestsOnArray($this->verbatimTests, 'format');
    }

    public function testVerbatimTextWithSpecialCharacters()
    {
        $tests = [
            [100, '$0', json_decode('"\u00a3100"')],
            [100, '{$}0', '$100'],
            [100, '{foo }0o', 'foo 100th'],
            [100, '0o{ foo}', '100th foo'],
            [100, '{$  }0', '$  100'],
            [100, '0{%}', '100%'],
            [100, '0{:}', '100:'],
            [100, '0{b}','100b']
        ];

        $this->languageManager->setCulture('en-GB');

        $this->runTestsOnArray($tests, 'format');
    }

}