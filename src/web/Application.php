<?php
namespace ys\web;

use Ys;

class Application extends \ys\base\Application
{
    

    public function run()
    {
        require  Ys::$app->getBasePath() . '/config/routes.php';
    }
}
