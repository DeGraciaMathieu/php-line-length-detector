<?php

namespace Tests\Unit\Renderers;

use Tests\TestCase;
use App\Dtos\Line;
use App\Dtos\Report;
use App\Dtos\Thresholds;
use App\Bags\FileBag;
use App\Bags\LengthBag;
use App\Bags\ExceededLineBag;

abstract class RendererTestCase extends TestCase
{
    protected function makeReport(bool $byFile = false, ?int $top = null): Report
    {
        $lengthBag = new LengthBag(new Thresholds([60, 30]));
        $exceededLineBag = new ExceededLineBag(30);
        $fileBag = $byFile ? new FileBag(tracksExceeded: true) : null;

        foreach ([new Line('a.php', 3, 20), new Line('a.php', 7, 70), new Line('b&c.php', 1, 40)] as $line) {
            $lengthBag->add($line->length);
            $exceeded = $exceededLineBag->add($line);
            $fileBag?->add($line, $exceeded);
        }

        return new Report($lengthBag, $exceededLineBag, $fileBag, $top);
    }
}
