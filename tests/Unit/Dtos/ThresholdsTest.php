<?php

namespace Tests\Unit\Dtos;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Dtos\Thresholds;
use InvalidArgumentException;

class ThresholdsTest extends TestCase
{
    #[Test]
    public function it_can_instantiate_itself_from_a_string(): void
    {
        $thresholds = Thresholds::fromString('50,30,10');

        $this->assertSame([
            '50' => 0,
            '30' => 0,
            '10' => 0,
        ], $thresholds->values);
    }

    #[Test]
    public function it_sorts_and_deduplicates_thresholds(): void
    {
        $thresholds = Thresholds::fromString('30, 50,30,10');

        $this->assertSame([
            50 => 0,
            30 => 0,
            10 => 0,
        ], $thresholds->values);
    }

    #[Test]
    public function it_rejects_a_non_numeric_threshold(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Thresholds::fromString('50,abc');
    }
}
