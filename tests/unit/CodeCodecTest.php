<?php

declare(strict_types=1);

namespace Lowseekai\PointRedempt\Tests\unit;

use Lowseekai\PointRedempt\Support\CodeCodec;
use PHPUnit\Framework\TestCase;

class CodeCodecTest extends TestCase
{
    public function test_generated_codes_have_expected_format_and_are_unique(): void
    {
        $codes = array_map(fn () => CodeCodec::generate(), range(1, 500));
        $this->assertCount(500, array_unique($codes));
        foreach ($codes as $code) $this->assertMatchesRegularExpression('/^LS-[2-9A-HJ-KM-NP-Z]{4}(?:-[2-9A-HJ-KM-NP-Z]{4}){3}$/', $code);
    }

    public function test_normalization_and_hash_are_stable(): void
    {
        $this->assertSame('LSABCD2345EFGH6789', CodeCodec::normalize(' ls-abcd-2345-efgh-6789 '));
        $this->assertSame(CodeCodec::hash('LS-ABCD-2345-EFGH-6789', 'secret'), CodeCodec::hash('lsabcd2345efgh6789', 'secret'));
        $this->assertSame('6789', CodeCodec::suffix('LS-ABCD-2345-EFGH-6789'));
    }
}
