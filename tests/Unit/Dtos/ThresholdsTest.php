<?php

namespace Tests\Unit\Aggregators;

use Tests\TestCase;
use App\Dtos\Thresholds;

class ThresholdsTest extends TestCase
{
    /**
     * @test
     */
    public function it_able_to_instantiate_itself_from_a_string(): void
    {
        $thresholds = Thresholds::fromString('50,30,10');

        $this->assertSame([
            '50' => 0,
            '30' => 0,
            '10' => 0,
        ], $thresholds->values);
    }
}
