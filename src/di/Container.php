<?php
namespace ys\di;

use ReflectionClass;
use Ys;
use ys\base\Component;

class Container extends Component
{
    private $singletons = [];
    private $params=[];
    private $definitions = [];
    private $reflections = [];
    /**
     * 依赖
     *
     * @var array
     */
    private $dependencies = [];

    public function get($class, $params = [], $config = [])
    {
        if (isset($this->_singletons[$class])) {
            // singleton
            return $this->_singletons[$class];
        } elseif (!isset($this->_definitions[$class])) {
            return $this->build($class, $params, $config);
        }
        $definition = $this->_definitions[$class];
    }

    public function set($class, $definition = [], array $params = [])
    {
    }
    /**
     * 构建
     *
     * @param [type] $class
     * @param [type] $params
     * @param [type] $config
     * @return void
     */
    public function build($class, $params, $config)
    {
        list ($reflection, $dependencies) = $this->getDependencies($class);
        foreach ($params as $index => $param) {
            $dependencies[$index] = $param;
        }
        $dependencies = $this->resolveDependencies($dependencies, $reflection);
        if (!$reflection->isInstantiable()) {
            throw new \Exception($reflection->name);
        }
        if (empty($config)) {
            return $reflection->newInstanceArgs($dependencies);
        }
        if (!empty($dependencies) && $reflection->implementsInterface('ys\base\Configurable')) {
            // set $config as the last parameter (existing one will be overwritten)
            $dependencies[count($dependencies) - 1] = $config;
            return $reflection->newInstanceArgs($dependencies);
        } else {
            $object = $reflection->newInstanceArgs($dependencies);
            foreach ($config as $name => $value) {
                $object->$name = $value;
            }
            return $object;
        }
    }

    protected function getDependencies($class)
    {
        if (isset($this->reflections[$class])) {
            return [$this->reflections[$class], $this->dependencies[$class]];
        }
        $dependencies = [];
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                if ($param->isDefaultValueAvailable()) {
                    $dependencies[] = $param->getDefaultValue();
                } else {
                    $c = $param->getClass();
                    $dependencies[] = Instance::of($c === null ? null : $c->getName());
                }
            }
        }
        $this->reflections[$class] = $reflection;
        $this->dependencies[$class] = $dependencies;
        return [$reflection, $dependencies];
    }

    protected function resolveDependencies($dependencies, $reflection = null)
    {
        foreach ($dependencies as $index => $dependency) {
            if ($dependency instanceof Instance) {
                if ($dependency->id !== null) {
                    $dependencies[$index] = $this->get($dependency->id);
                } elseif ($reflection !== null) {
                    $name = $reflection->getConstructor()->getParameters()[$index]->getName();
                    $class = $reflection->getName();
                    throw new \Exception("Missing required parameter \"$name\" when instantiating \"$class\".");
                }
            }
        }
        return $dependencies;
    }
}
