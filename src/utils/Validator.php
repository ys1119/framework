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
    private $reasons = [];
    private $data;
    private $rules;
    public function __construct($data, $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->reasons = [
            'email' => 'The :attribute format is invalid.',
            'ip' => 'The :attribute format is invalid.',
            'url' => 'The :attribute format is invalid.',
            'min' => 'The :attribute must be at least :min.',
            'max' => 'The :attribute may not be greater than :max.',
            'required' => 'The :attribute field is required.',
            'numeric' => 'The :attribute field must be number.',
            'integer' => 'The :attribute field must be integer.',
        ];
        $this->fire();
    }
    public function fire()
    {
        foreach ($this->rules as $attribute => $rule) {
            foreach (explode('|', $rule) as $item) {
                $detial = explode(':', $item);
                $data = isset($this->data[$attribute])?$this->data[$attribute]:null;
                if (count($detial) > 1) {
                    $reason = $this->{$detial[0]}($data, $detial[1]);
                } else {
                    $reason = $this->$item($data);
                }
                if ($reason !== true) {
                    $this->errors[] = str_replace(':attribute', $attribute, $reason);
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
        } elseif ((is_array($value) || $value instanceof Countable) && count($value) < 1) {
            return false;
        } elseif ($value instanceof File) {
            return (string) $value->getPath() != '';
        }

        return true;
        // return !$value ? $this->reasons['required'] : true;
    }
    protected function email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) ? true : $this->reasons['email'];
    }
    protected function min($value, $min)
    {
        return mb_strlen($value, 'UTF-8') >= $min ? true : str_replace(':min', $min, $this->reasons['min']);
    }
    protected function max($value, $max)
    {
        return mb_strlen($value, 'UTF-8') <= $max ? true : str_replace(':max', $max, $this->reasons['max']);
    }
    protected function numeric($value)
    {
        return is_numeric($value) ? true : $this->reasons['numeric'];
    }
    protected function integer($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? true : $this->reasons['integer'];
    }
    protected function string($value){
        return is_string($value);
    }

    protected function ip($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP) !==false?true:$this->reasons['ip'];
    }

    public function ipv4($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }
    public function ipv6($value)
    {
        return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    public function json($value)
    {
        if (! is_scalar($value) && ! method_exists($value, '__toString')) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !==false?true:$this->reasons['ip'];
    }

    public function date($value)
    {
        if ($value instanceof \DateTime) {
            return true;
        }
        $date = date_parse($value);
        return checkdate($date['month'], $date['day'], $date['year']);
    }

    public function in($value, $range)
    {
        var_dump($range);
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

    public function array($value)
    {
        return is_array($value);
    }

    public function __call($method, $parameters)
    {
        throw new \UnexpectedValueException("Validate rule [$method] does not exist!");
    }
}
$data = [

];
$validator = new Validator($data, [
    // 'customer_number' => 'required|numeric|integer',
    'name' => 'required|min:2|max:25',
    'sex' => 'required|integer|in:[1,2]',
    // 'id_type' => 'required|integer',
    // 'id_number' => 'required',
    // 'phone' => 'required',
    // 'email' => 'email',
]);
var_dump($validator->errors);
