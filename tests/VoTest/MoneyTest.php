<?php

namespace VoTest;

use NumberFormatter;
use Vo\Money;

class MoneyTest extends \PHPUnit_Framework_TestCase
{
    public function testAddTwoSmallNumbers()
    {
        $m1 = new Money(123.45);
        $m2 = new Money(678.90);

        $this->assertSame(
            '802.35000000000000000000',
            $m1->add($m2)->getAmount()
        );
    }

    public function testAddThreeSmallNumbers()
    {
        $m1 = new Money(123.45);
        $m2 = new Money(678.90);
        $m3 = new Money(987.65);

        $this->assertSame(
            '1790.00000000000000000000',
            $m1->add($m2)->add($m3)->getAmount()
        );
    }

    public function testAddLarge()
    {
        $m1 = new Money('1234567890123456789.123456789');
        $m2 = new Money('9876543210987654321.987654321');

        $this->assertSame(
            '11111111101111111111.11111111000000000000',
            $m1->add($m2)->getAmount()
        );
    }

    public function testSubTwoSmallNumbers()
    {
        $m1 = new Money(123.45);
        $m2 = new Money(678.90);

        $this->assertSame(
            '-555.45000000000000000000',
            $m1->sub($m2)->getAmount()
        );
    }

    public function testSubThreeSmallNumbers()
    {
        $m1 = new Money(123.45);
        $m2 = new Money(678.90);
        $m3 = new Money(987.65);

        $this->assertSame(
            '-1543.10000000000000000000',
            $m1->sub($m2)->sub($m3)->getAmount()
        );
    }

    public function testSubLarge()
    {
        $m1 = new Money('1234567890123456789.123456789');
        $m2 = new Money('9876543210987654321.987654321');

        $this->assertSame(
            '-8641975320864197532.86419753200000000000',
            $m1->sub($m2)->getAmount()
        );
    }

    public function testDivTwoSmallNumbers()
    {
        $m1 = new Money(123.45);
        $m2 = new Money(678.90);

        $this->assertSame(
            '0.18183826778612461334',
            $m1->div($m2)->getAmount()
        );
    }

    public function testDivThreeSmallNumbers()
    {
        $m1 = new Money(123.45);
        $m2 = new Money(678.90);
        $m3 = new Money(987.65);

        $this->assertSame(
            '0.00018411205162367702',
            $m1->div($m2)->div($m3)->getAmount()
        );
    }

    public function testDivLarge()
    {
        $m1 = new Money('1234567890123456789.123456789');
        $m2 = new Money('9876543210987654321.987654321');

        $this->assertSame(
            '0.12499999886093750001',
            $m1->div($m2)->getAmount()
        );
    }

    public function testMulTwoSmallNumbers()
    {
        $m1 = new Money(123.45);
        $m2 = new Money(678.90);

        $this->assertSame(
            '83810.205',
            $m1->mul($m2)->getAmount()
        );
    }

    public function testMulThreeSmallNumbers()
    {
        $m1 = new Money(123.45);
        $m2 = new Money(678.90);
        $m3 = new Money(987.65);

        $this->assertSame(
            '82775148.96825',
            $m1->mul($m2)->mul($m3)->getAmount()
        );
    }

    public function testMulLarge()
    {
        $m1 = new Money('1234567890123456789.123456789');
        $m2 = new Money('9876543210987654321.987654321');

        $this->assertSame(
            '12193263113702179524813290633609205911.347203169112635269',
            $m1->mul($m2)->getAmount()
        );
    }

    public function testRound()
    {
        $m = new Money('12193263113702179524813290633609205911.347203169112635269');

        $map = array(
            0 =>  '12193263113702179524813290633609205911',
            1 =>  '12193263113702179524813290633609205911.3',
            2 =>  '12193263113702179524813290633609205911.35',
            4 =>  '12193263113702179524813290633609205911.3472',
            15 => '12193263113702179524813290633609205911.347203169112635'
        );

        foreach ($map as $places => $expected) {
            $this->assertSame(
                $expected,
                $m->round($places)
            );
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddCurrencyMismatch()
    {
        $m1 = new Money(45, 'EUR');
        $m2 = new Money(50, 'USD');

        $m2->add($m1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testDivCurrencyMismatch()
    {
        $m1 = new Money(45, 'EUR');
        $m2 = new Money(50, 'USD');

        $m2->div($m1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMulCurrencyMismatch()
    {
        $m1 = new Money(45, 'EUR');
        $m2 = new Money(50, 'USD');

        $m2->mul($m1);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSubCurrencyMismatch()
    {
        $m1 = new Money(45, 'EUR');
        $m2 = new Money(50, 'USD');

        $m2->sub($m1);
    }

    public function testFormatPositiveUSDInEnUs()
    {
        $m = new Money(1077.701, 'USD');
        $m->setFormatter(
            new NumberFormatter(
                'en-US',
                NumberFormatter::CURRENCY
            )
        );

        $this->assertSame(
            '$1,077.70',
            $m->format()
        );
    }

    public function testNegativeFormatUSDInEnUs()
    {
        $m = new Money('-1077.701', 'USD');
        $m->setFormatter(
            new NumberFormatter(
                'en-US',
                NumberFormatter::CURRENCY
            )
        );

        $this->assertSame(
            '($1,077.70)',
            $m->format()
        );
    }

    public function testPositiveFormatEURInEnUs()
    {
        $m = new Money(1077.701, 'EUR');
        $m->setFormatter(
            new NumberFormatter(
                'en-US',
                NumberFormatter::CURRENCY
            )
        );

        $this->assertSame(
            '€1,077.70',
            $m->format()
        );
    }

    public function testNegativeFormatEURInEnUs()
    {
        $m = new Money('-1077.701', 'EUR');
        $m->setFormatter(
            new NumberFormatter(
                'en-US',
                NumberFormatter::CURRENCY
            )
        );

        $this->assertSame(
            '(€1,077.70)',
            $m->format()
        );
    }

    public function testPositiveFormatEURInDeDe()
    {
        $m = new Money(1077.701, 'EUR');
        $m->setFormatter(
            new NumberFormatter(
                'de-DE',
                NumberFormatter::CURRENCY
            )
        );

        $this->assertSame(
            '1.077,70 €',
            $m->format()
        );
    }
}
