<?php

declare(strict_types=1);

namespace App\Renderers;

use Termwind\HtmlRenderer;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View\Factory as ViewFactory;
use App\Dtos\Report;

class ViewRenderer implements Renderer
{
    public function __construct(
        private ViewFactory $view,
        private HtmlRenderer $htmlRenderer,
    ) {}

    public function display(Report $report): void
    {
        $html = $this->makeHtml($report);

        $this->renderHtml($html);
    }

    protected function makeHtml(Report $report): ViewContract
    {
        return $this->view->make('inspect', [
            'lengthBag' => $report->lengthBag,
            'exceededLineBag' => $report->exceededLineBag,
            'fileBag' => $report->fileBag,
            'top' => $report->top,
        ]);
    }

    private function renderHtml(ViewContract $html): void
    {
        $this->htmlRenderer->render($html->render(), OutputInterface::OUTPUT_NORMAL);
    }
}
