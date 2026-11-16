<?php

namespace App\Renderers;

use Termwind\HtmlRenderer;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View\Factory as ViewFactory;
use App\Bags\LengthBag;
use App\Bags\ExceededLineBag;

class ViewRenderer
{
    public function __construct(
        private ViewFactory $view,
        private HtmlRenderer $htmlRenderer,
    ) {}

    public function display(LengthBag $lengthBag, ?ExceededLineBag $exceededLineBag, ?int $top): void
    {
        $html = $this->makeHtml($lengthBag, $exceededLineBag, $top);

        $this->renderHtml($html);
    }

    protected function makeHtml(LengthBag $lengthBag, ?ExceededLineBag $exceededLineBag, ?int $top): ViewContract
    {
        return $this->view->make('inspect', [
            'lengthBag' => $lengthBag,
            'exceededLineBag' => $exceededLineBag,
            'top' => $top,
        ]);
    }

    private function renderHtml(ViewContract $html): void
    {
        $this->htmlRenderer->render($html, OutputInterface::OUTPUT_NORMAL);
    }
}
