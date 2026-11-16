<?php

declare(strict_types=1);

namespace App\Renderers;

use Termwind\HtmlRenderer;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\View\Factory as ViewFactory;
use App\Bags\LengthBag;
use App\Bags\ExceededLineBag;
use App\Bags\FileBag;

class ViewRenderer
{
    public function __construct(
        private ViewFactory $view,
        private HtmlRenderer $htmlRenderer,
    ) {}

    public function display(
        LengthBag $lengthBag,
        ?ExceededLineBag $exceededLineBag,
        ?FileBag $fileBag,
        ?int $top,
    ): void
    {
        $html = $this->makeHtml($lengthBag, $exceededLineBag, $fileBag, $top);

        $this->renderHtml($html);
    }

    protected function makeHtml(
        LengthBag $lengthBag,
        ?ExceededLineBag $exceededLineBag,
        ?FileBag $fileBag,
        ?int $top,
    ): ViewContract
    {
        return $this->view->make('inspect', [
            'lengthBag' => $lengthBag,
            'exceededLineBag' => $exceededLineBag,
            'fileBag' => $fileBag,
            'top' => $top,
        ]);
    }

    private function renderHtml(ViewContract $html): void
    {
        $this->htmlRenderer->render($html->render(), OutputInterface::OUTPUT_NORMAL);
    }
}
