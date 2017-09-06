<?php
namespace ys\di;

use Ys;
use Closure;
use ys\base\Component;

class ServiceLocator extends Component
{
    private $components = [];
    private $definitions = [];
    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        if ($this->has($name)) {
            return true;
        } else {
            return parent::__isset($name);
        }
    }
    
    public function has($id, $checkInstance = false)
    {
        return $checkInstance ? isset($this->components[$id]) : isset($this->definitions[$id]);
    }

    public static function getInstance()
    {
        $class = get_called_class();
        return isset(Ys::$app->loadedModules[$class]) ? Ys::$app->loadedModules[$class] : null;
    }


    public static function setInstance($instance)
    {
        if ($instance === null) {
            unset(Ys::$app->loadedModules[get_called_class()]);
        } else {
            Ys::$app->loadedModules[get_class($instance)] = $instance;
        }
    }

    public function get($id, $throwException = true)
    {
        if (isset($this->components[$id])) {
            return $this->components[$id];
        }
       
        if (isset($this->definitions[$id])) {
            $definition = $this->definitions[$id];
            if (is_object($definition) && !$definition instanceof \Closure) {
                return $this->components[$id] = $definition;
            } else {
                return $this->components[$id] = Ys::createObject($definition);
            }
        } elseif ($throwException) {
            throw new \Exception("Unknown component ID: $id");
        } else {
            return null;
        }
    }

    public function set($id, $definition)
    {
        unset($this->components[$id]);

        if ($definition === null) {
            unset($this->definitions[$id]);
            return;
        }
        
        if (is_object($definition) || is_callable($definition, true)) {
            // an object, a class name, or a PHP callable
            $this->definitions[$id] = $definition;
        } elseif (is_array($definition)) {
            // a configuration array
            if (isset($definition['class'])) {
                $this->definitions[$id] = $definition;
            } else {
                throw new \Exception("The configuration for the \"$id\" component must contain a \"class\" element.");
            }
        } else {
            throw new \Exception("Unexpected configuration type for the \"$id\" component: " . gettype($definition));
        }
    }


   
}
