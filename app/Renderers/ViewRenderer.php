<?php

namespace App\Renderers;

use Termwind\HtmlRenderer;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View\Factory as ViewFactory;
use App\Bags\LengthBag;

class ViewRenderer
{
    public function __construct(
        private ViewFactory $view,
        private HtmlRenderer $htmlRenderer,
    ) {}

    public function display(LengthBag $lengthBag): void
    {
        $html = $this->makeHtml($lengthBag);

        $this->renderHtml($html);
    }

    protected function makeHtml(LengthBag $lengthBag): ViewContract
    {
        return $this->view->make('inspect', [
            'lengthBag' => $lengthBag,
        ]);
    }

    private function renderHtml(ViewContract $html): void
    {
        $this->htmlRenderer->render($html, OutputInterface::OUTPUT_NORMAL);
    }
}
