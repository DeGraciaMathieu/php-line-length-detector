<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Bags\FileBag;
use App\Bags\LengthBag;
use App\Bags\ExceededLineBag;

class Report
{
    public function __construct(
        public readonly LengthBag $lengthBag,
        public readonly ?ExceededLineBag $exceededLineBag,
        public readonly ?FileBag $fileBag,
        public readonly ?int $top,
    ) {}
}
