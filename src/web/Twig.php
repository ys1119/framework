<?php
namespace ys\web;

use Ys;
use ys\route\Route;

/**
 * Class Twig
 */
class Twig
{
    public $view;
    public $data;
    public $twig;
    
    /**
     * Twig constructor.
     * @param $view
     * @param $data
     */
    public function __construct($view, $data)
    {
        $loader = new \Twig_Loader_Filesystem(Ys::$app->getBasePath().'/view/');
        $this->twig = new \Twig_Environment($loader, [
            'cache' => Ys::$app->getBasePath() . '/cache/views/',
            'debug' => APP_DEBUG
        ]);
        $this->twig->addExtension(new \Twig_Extension_Debug());
        $function = new \Twig_SimpleFunction('path',function(string $name,$parameters=[]){ return Route::path($name,$parameters);});
        $this->twig->addFunction( $function );
        $this->view = $view;
        $this->data = $data;
    }

   
    /**
     * @param $view
     * @param array $data
     * @return Twig
     */
    public static function render($view, $data = [])
    {
        return new Twig($view, $data);
    }
    public function __destruct()
    {
        $this->twig->display($this->view, $this->data);
    }
}
