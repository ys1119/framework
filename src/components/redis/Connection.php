<?php
namespace ys\components\redis;

use YS;
use ys\base\Component;
use Predis\Client;

class Connection extends Component
{
    public $config=[];

    private $redis = false;

    public function init()
    {
        $this->redis = new Client($this->config);
    }

    public function __call($name, $params)
    {
        return call_user_func_array([$this->redis,$name],$params);
    }
}
