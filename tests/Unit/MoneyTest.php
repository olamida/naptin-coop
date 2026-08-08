<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_add_avoids_float_drift(): void
    {
        $total = 0.0;
        for ($i = 0; $i < 1000; $i++) {
            $total = Money::add($total, 0.10);
        }

        $this->assertEquals(100.0, $total);
    }

    public function test_sub(): void
    {
        $this->assertSame(9999.99, Money::sub(20000.0, 10000.01));
        $this->assertSame(0.01, Money::sub(0.02, 0.01));
    }

    public function test_mul_rounds_to_two_decimals(): void
    {
        $this->assertEquals(2250.0, Money::mul(15000, 0.15));
        $this->assertEquals(3.33, Money::mul(1.11, 3));
    }

    public function test_div_and_percent(): void
    {
        $this->assertEquals(8333.33, round(Money::div(100000, 12), 2));
        $this->assertEquals(0.005, Money::div(0.5, 100));
        $this->assertEquals(25000.0, Money::percent(100000, 25));
        $this->assertEquals(0.0, Money::div(100, 0));
    }

    public function test_compare_helpers(): void
    {
        $this->assertTrue(Money::eq(100.0, 100.00));
        $this->assertTrue(Money::gt(101.0, 100.0));
        $this->assertTrue(Money::gte(100.0, 100.0));
        $this->assertTrue(Money::lt(99.0, 100.0));
        $this->assertTrue(Money::lte(100.0, 100.0));
    }

    public function test_min_max_sum(): void
    {
        $this->assertEquals(1.5, Money::min(1.5, 2.5, 3.5));
        $this->assertEquals(3.5, Money::max(1.5, 2.5, 3.5));
        $this->assertEquals(6.0, Money::sum([1.5, 2.25, 2.25]));
    }

    public function test_abs(): void
    {
        $this->assertEquals(0.05, Money::abs(-0.05));
    }

    public function test_sub_does_not_produce_floating_point_artefacts(): void
    {
        $this->assertSame(0.0, Money::sub(0.3, 0.3));
        $this->assertSame(0.1, Money::sub(0.3, 0.2));
    }
}
