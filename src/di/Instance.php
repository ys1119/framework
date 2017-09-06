<?php
namespace ys\di;

use Ys;

class Instance
{
    public $id;
    
    
    protected function __construct($id)
    {
        $this->id = $id;
    }

    public static function of($id)
    {
        return new static($id);
    }

    public static function ensure($reference, $type = null, $container = null)
    {
        if (is_array($reference)) {
            $class = isset($reference['class']) ? $reference['class'] : $type;
            if (!$container instanceof Container) {
                $container = Yii::$container;
            }
            unset($reference['class']);
            $component = $container->get($class, [], $reference);
            if ($type === null || $component instanceof $type) {
                return $component;
            } else {
                throw new \Exception('Invalid data type: ' . $class .'. ' . $type . ' is expected.');
            }
        } elseif (empty($reference)) {
            throw new \Exception('The required component is not specified.');
        }
        if (is_string($reference)) {
            $reference = new static($reference);
        } elseif ($type === null || $reference instanceof $type) {
            return $reference;
        }

        if ($reference instanceof self) {
            try {
                $component = $reference->get($container);
            } catch (\ReflectionException $e) {
                throw new \ReflectionException('Failed to instantiate component or class "' . $reference->id . '".', 0, $e);
            }
            if ($type === null || $component instanceof $type) {
                return $component;
            } else {
                throw new \Exception('"' . $reference->id . '" refers to a ' . get_class($component) . " component. $type is expected.");
            }
        }

        $valueType = is_object($reference) ? get_class($reference) : gettype($reference);
        throw new \Exception("Invalid data type: $valueType. $type is expected.");
    }

    public function get(Container $container)
    {
        if ($container) {
            return $container->get($this->id);
        }
        if (Ys::$app && Ys::$app->has($this->id)) {
            return Ys::$app->get($this->id);
        } else {
            return Ys::$container->get($this->id);
        }
    }


    public static function __set_state($state)
    {
        if (!isset($state['id'])) {
            throw new \Exception('Failed to instantiate class "Instance". Required parameter "id" is missing');
        }

        return new self($state['id']);
    }
}
