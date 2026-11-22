<?php

namespace Tests\Unit;

use App\Rules\TeleBirrNumber;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\TestCase;

class TeleBirrNumberTest extends TestCase
{
    private function passes(mixed $value): bool
    {
        $failed = false;
        $rule = new TeleBirrNumber();

        $rule->validate('phone', $value, function () use (&$failed): void {
            $failed = true;
        });

        return ! $failed;
    }

    public function test_valid_ethiopian_telebirr_number_passes(): void
    {
        $this->assertTrue($this->passes('0911234567'));
    }

    public function test_malformed_numbers_fail_direct_rule_validation(): void
    {
        $this->assertFalse($this->passes('911234567'));
        $this->assertFalse($this->passes('+251911234567'));
        $this->assertFalse($this->passes('09112345678'));
        $this->assertFalse($this->passes('phone'));
    }
}
