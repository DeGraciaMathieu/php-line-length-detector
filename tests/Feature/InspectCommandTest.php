<?php

namespace Tests\Feature;

use Tests\TestCase;

class InspectCommandTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_analyse_folder(): void
    {
        $this->artisan('inspect ' . __DIR__ . '/')
            ->expectsOutput('❀ PHP Line Lenght Detector ❀')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function it_succeeds_when_no_line_exceeds_the_max(): void
    {
        $this->artisan('inspect ' . __DIR__ . '/ --max=1000')
            ->assertSuccessful();
    }

    /**
     * @test
     */
    public function it_fails_when_a_line_exceeds_the_max(): void
    {
        $this->artisan('inspect ' . __DIR__ . '/ --max=10')
            ->assertFailed();
    }
}
