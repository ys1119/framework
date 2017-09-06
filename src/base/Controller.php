<?php
namespace ys\base;

use Ys;

class Controller
{
    protected $controller;
    public function __construct()
    {
        $this->controller = static::class;
        $this->init();
    }

    public function init()
    {
       // echo Ys::$app->getBasePath();die;
    }
}
