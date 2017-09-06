<?php
namespace ys\web;

use Ys;

class View
{
    const VIEW_BASE_PATH = 'view';

    public $view;
    public $data;

    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * 创建视图
     *
     * @param [type] $viewName
     * @return void
     */
    public static function make($viewName)
    {
        if (!$viewName) {
            throw new \InvalidArgumentException("View name can not be empty!");
        } else {
            $viewFilePath = self::getFilePath($viewName);
            if (is_file($viewFilePath)) {
                return new View($viewFilePath);
            } else {
                throw new \UnexpectedValueException("View file does not exist!:".$viewFilePath);
            }
        }
    }

    /**
     * 变量传递
     *
     * @param [type] $key
     * @param [type] $value
     * @return void
     */
    public function with($key, $value = null)
    {
        $this->data[$key] = $value;
        return $this;
    }



    public function __call($method, $parameters)
    {
        if (starts_with($method, 'with')) {
            return $this->with(snake_case(substr($method, 4)), $parameters[0]);
        }
        throw new \BadMethodCallException("Function [{$method}] does not exist!");
    }
    /**
     * 视图路径
     *
     * @param [type] $viewName
     * @return void
     */
    private static function getFilePath($viewName)
    {
        $filePath = str_replace('.', '/', $viewName);
        return Ys::$app->getBasePath().'/view/'. $filePath . '.php';
    }

    public function __destruct()
    {
        if ($this->data) {
            extract($this->data);
        }
        require_once $this->view;
    }
}
