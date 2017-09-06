<?php
namespace ys\base;

use Ys;

abstract class Application extends Module
{
    public $language = 'zh-CN';
    public $charset = 'UTF-8';

    public $loadedModules = [];

    public function __construct(array $config = [])
    {
        Ys::$app = $this;
        static::setInstance($this);
        if(isset($config['db'])){
            $capsule = new \Illuminate\Database\Capsule\Manager ;
            $capsule->addConnection($config['db']);
            //Set the event dispatcher used by Eloquent models... (optional)
            $capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));
            //Make this Capsule instance available globally via static methods... (optional)
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            unset($config['db']);
        }
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        }
        Component::__construct($config);
    }

    public function setContainer($config)
    {
        Ys::configure(Ys::$container, $config);
    }

    public function coreComponents()
    {
        return [
            'log' => ['class' => 'ys\log\Dispatcher'],
        ];
    }
}
