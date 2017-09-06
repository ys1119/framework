<?php
namespace ys\utils;

/**
 * http://laravelacademy.org/post/3279.html#rule-accepted
 * vendor/laravel/framework/src/Illuminate/Validation/Concerns/ValidatesAttributes.php
 * Undocumented class
 */
class Validator
{
    public $success = true;
    public $errors = [];

    private $data;
    private $rules;
    private $reasons = [
        'required' => 'The :attribute field is required.',
        'required_if' => 'The :attribute field is required.',
        'email' => 'The :attribute format is invalid.',
        'numeric' => 'The :attribute field must be number.',
        'integer' => 'The :attribute field must be integer.',
        'string' => 'The :attribute field must be string.',
        'array' => 'The :attribute field must be array.',
        'json' => 'The :attribute field must be json.',
        'url' => 'The :attribute field must be url.',
        'date' => 'The :attribute field must be date.',
        'ip' => 'The :attribute field must be ip.',
        'ip4' => 'The :attribute field must be ip4.',
        'ip6' => 'The :attribute field must be ip6.',
        'alpha' => 'The :attribute format is invalid.',
        'alpha_dash' => 'The :attribute format is invalid.',
        'alpha_num' => 'The :attribute format is invalid.',
        'min' => 'The :attribute must be at least :min.',
        'max' => 'The :attribute may not be greater than :max.',
        'length' => 'The :attribute field out of length :length.',
        'range' => 'The :attribute field out of range :range.',
    ];
    public function __construct($data, $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->start();
    }

    public function start()
    {
        foreach ($this->rules as $attribute => $rule) {
            foreach (explode('|', $rule) as $item) {
                $detial = explode(':', $item);
                $data = isset($this->data[$attribute])?$this->data[$attribute]:null;
                if (count($detial) === 1) {
                    $reason = $this->$item($data);
                    if ($reason === false) {
                        $reason = isset($this->reasons[$item])===true? $this->reasons[$item]:'The :attribute format is invalid.';
                        $this->errors[] = str_replace(':attribute', $attribute, $reason);
                    }
                } elseif (count($detial) === 2) {
                    $reason = $this->{$detial[0]}($data,explode(',', $detial[1]));
                    if ($reason === false) {
                        $reason = isset($this->reasons[$detial[0]])===true? $this->reasons[$detial[0]]:'The :attribute format is invalid.';
                        $this->errors[] = str_replace(':'.$detial[0], '['.$detial[1].']', str_replace(':attribute', $attribute, $reason));
                    }
                } else {
                    throw new \LengthException(" $attribute parameters");
                }
            }
        }
        if (count($this->errors)) {
            $this->success = false;
        }
    }
    protected function required($value)
    {
        if (is_null($value)) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif ((is_array($value) || $value instanceof \Countable) && count($value) < 1) {
            return false;
        } elseif ($value instanceof \File) {
            return (string) $value->getPath() != '';
        }
        return true;
    }

    /**
     * required_if:anotherfield,value,…

    * 验证字段在另一个字段等于指定值value时是必须的
     *
     * @param [type] $value
     * @param [type] $parameters
     * @return void
     * @author S.Y <yangsheng@yahoo.com>
     * @version 1.0.0
     */
    protected function required_if($value, $parameters)
    {
        $this->requireParameterCount(2, $parameters, 'required_if');
        if($this->data[$parameters[0]]==$parameters[1]){
            return $this->required($value);
        }
        return true;
    }

    protected function email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    protected function numeric($value)
    {
        return is_numeric($value);
    }
    protected function integer($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }
    protected function string($value)
    {
        return is_string($value);
    }

    public function array($value)
    {
        return is_array($value);
    }

    public function json($value)
    {
        if (! is_scalar($value) && ! method_exists($value, '__toString')) {
            return false;
        }
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function ip($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !==false;
    }
    public function ipv4($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }
    public function ipv6($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    protected function url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !==false;
    }
    /**
     * 验证字段必须是一个基于PHP strtotime函数的有效日期
     *
     * @param [type] $value
     * @return void
     * @author S.Y <yangsheng@yahoo.com>
     * @version 1.0.0
     */
    public function date($value)
    {
        if ($value instanceof \DateTime) {
            return true;
        }
        $date = date_parse($value);
        return checkdate($date['month'], $date['day'], $date['year']);
    }
    /**
     * 字段仅全数为字母字串时通过验证。
     *
     * @param [type] $value
     * @return void
     */
    public function alpha($value)
    {
        return is_string($value) && preg_match('/^[\pL\pM]+$/u', $value);
    }
    /**
     * 字段值仅允许字母、数字、破折号（-）以及底线（_）
     *
     * @param [type] $value
     * @return void
     */
    public function alpha_dash($value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }
        return preg_match('/^[\pL\pM\pN_-]+$/u', $value) > 0;
    }

    /**
     * 字段值仅允许字母、数字
     *
     * @return void
     */
    public function alpha_num($value)
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }
        return preg_match('/^[\pL\pM\pN]+$/u', $value) > 0;
    }

    /**
     * 验证两字段相等
     *
     * @param [type] $value
     * @param [type] $parameters
     * @return void
     * @author S.Y <yangsheng@yahoo.com>
     * @version 1.0.0
     */
    public function confirmed($value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'min');
        return $value === $this->data[$parameters[0]];
    }
   
    protected function min($value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'min');
        return $value >= $parameters === true;
    }
    protected function max($value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'max');
        return $value <= $parameters === true;
    }

    protected function length($value, $parameters)
    {
        
        $this->requireParameterCount(2, $parameters, 'length');
        $length = mb_strlen($value, 'UTF-8');
        return $length >= $parameters[0] === true && $length <= $parameters[1]===true;
    }
   

    public function in($value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'in');
        return in_array($value, $parameters) === true;
    }

    public function not_in($value, $parameters)
    {
        $this->requireParameterCount(1, $parameters, 'in');
        return in_array($value, $parameters)===false;
    }

    /**
     * Require a certain number of parameters to be present.
     *
     * @param [type] $count
     * @param [type] $parameters
     * @param [type] $rule
     * @return void
     * @author S.Y <yangsheng@yahoo.com>
     * @version 1.0.0
     */
    protected function requireParameterCount($count, $parameters, $rule)
    {
        if (count($parameters) < $count) {
            throw new \InvalidArgumentException("Validation rule {$rule} requires at least {$count} parameters.");
        }
    }

    public function __call($method, $parameters)
    {
        throw new \UnexpectedValueException("Validate rule [{$method}] does not exist!");
    }
}
