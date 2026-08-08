<?php

namespace App\Support;

/**
 * Arbitrary-precision money arithmetic backed by bcmath.
 *
 * All methods accept floats/strings/ints (money columns are decimal(15,2) so
 * values are normalised to two-decimal strings before any operation), which
 * avoids the float accumulation errors that creep into ledgers over time.
 */
class Money
{
    private const SCALE = 2;

    private const OP_SCALE = 10;

    public static function add(float|string|int $a, float|string|int $b): float
    {
        return (float) bcadd(self::num($a), self::num($b), self::SCALE);
    }

    public static function sub(float|string|int $a, float|string|int $b): float
    {
        return (float) bcsub(self::num($a), self::num($b), self::SCALE);
    }

    /**
     * Multiply two values with full precision (operands are NOT rounded to 2dp —
     * a ratio like 0.025 must survive), then round the result to 2dp.
     */
    public static function mul(float|string|int $a, float|string|int $b): float
    {
        return round((float) bcmul(self::str($a), self::str($b), self::OP_SCALE), self::SCALE);
    }

    /**
     * Divide two values; a zero divisor yields zero. Result keeps full precision
     * (callers round when they need a 2dp monetary result).
     */
    public static function div(float|string|int $a, float|string|int $b): float
    {
        $b = self::str($b);

        if ((float) $b == 0) {
            return 0.0;
        }

        return (float) bcdiv(self::str($a), $b, self::OP_SCALE);
    }

    /**
     * percentage of an amount (e.g. percent(100000, 5) == 5000.00).
     */
    public static function percent(float|string|int $amount, float|string|int $percentage): float
    {
        return self::mul($amount, self::div($percentage, 100));
    }

    public static function compare(float|string|int $a, float|string|int $b): int
    {
        return bccomp(self::num($a), self::num($b), self::SCALE);
    }

    public static function eq(float|string|int $a, float|string|int $b): bool
    {
        return self::compare($a, $b) === 0;
    }

    public static function gt(float|string|int $a, float|string|int $b): bool
    {
        return self::compare($a, $b) === 1;
    }

    public static function gte(float|string|int $a, float|string|int $b): bool
    {
        return self::compare($a, $b) >= 0;
    }

    public static function lt(float|string|int $a, float|string|int $b): bool
    {
        return self::compare($a, $b) === -1;
    }

    public static function lte(float|string|int $a, float|string|int $b): bool
    {
        return self::compare($a, $b) <= 0;
    }

    public static function abs(float|string|int $a): float
    {
        return (float) abs((float) self::num($a));
    }

    public static function min(float|string|int ...$values): float
    {
        $min = array_shift($values);

        foreach ($values as $value) {
            if (self::lt($value, $min)) {
                $min = $value;
            }
        }

        return (float) self::num($min);
    }

    public static function max(float|string|int ...$values): float
    {
        $max = array_shift($values);

        foreach ($values as $value) {
            if (self::gt($value, $max)) {
                $max = $value;
            }
        }

        return (float) self::num($max);
    }

    public static function sum(array $values): float
    {
        $total = 0.0;

        foreach ($values as $value) {
            $total = self::add($total, $value);
        }

        return $total;
    }

    private static function num(float|string|int $value): string
    {
        return number_format((float) $value, self::SCALE, '.', '');
    }

    /**
     * Lossless string form for multiplication operands — keeps full float
     * precision (up to 10 decimal places) instead of rounding to 2dp.
     */
    private static function str(float|string|int $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            return $value;
        }

        return rtrim(rtrim(sprintf('%.10F', $value), '0'), '.');
    }
}
