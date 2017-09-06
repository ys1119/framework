<?php
namespace ys\log;

use Ys;
use ys\base\Component;
use Monolog\Logger;
use Monolog\Handler\RedisHandler;

class Dispatcher extends Component
{

    public $log;

    public function init()
    {
        $this->log = new Logger(static::class);
        $this->log->pushProcessor(new \Monolog\Processor\MemoryUsageProcessor);
        $this->log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor);
        $this->log->pushProcessor(new \Monolog\Processor\WebProcessor);
        $this->log->pushProcessor(new \Monolog\Processor\MemoryPeakUsageProcessor);
        $this->log->pushHandler(new \Monolog\Handler\BrowserConsoleHandler);
        $this->log->pushHandler(new \Monolog\Handler\StreamHandler(Ys::$app->getBasePath().'/logs/app.log', Logger::WARNING));
    }

    public function __call($name, $params)
    {
        return call_user_func_array([$this->log,$name], $params);
    }
}
