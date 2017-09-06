<?php
namespace ys\web;

use Ys;

class Controller extends \ys\base\Controller
{

    protected function make(string $view)
    {
        return View::make($view);
    }

    protected function render(string $view, array $data = [])
    {
        Twig::render($view, $data);
    }

}
