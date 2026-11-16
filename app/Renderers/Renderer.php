<?php

declare(strict_types=1);

namespace App\Renderers;

use App\Dtos\Report;

interface Renderer
{
    public function display(Report $report): void;
}
