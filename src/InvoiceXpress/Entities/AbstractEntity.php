<?php


namespace InvoiceXpress\Entities;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvoiceXpress\Auth;
use InvoiceXpress\Exceptions\InvalidAuth;
use InvoiceXpress\Utils\ArrayUtil;
use InvoiceXpress\Utils\CastUtil;
use InvoiceXpress\Utils\ReflectionUtil;
use InvoiceXpress\Validation\JsonValidator;
use stdClass;
use function is_array;

/**
 * Class AbstractEntity
 *
 * Abstract Entity Class.
 *
 * @package InvoiceXpress\Entities
 *
 * @property string id
 */
class AbstractEntity
{

    /**
     * Defines the array entry point with dot notation
     *
     * @var string CONTAINER
     */
    public const CONTAINER = null;

    /**
     * Defines the URL identifier to be replaced
     *
     * @var string ITEM_IDENTIFIER
     */
    public const ITEM_IDENTIFIER = '';

    /**
     * Defines allowed UPDATE keys
     *
     * @var array UPDATE_KEYS
     */
    public const UPDATE_KEYS = [];

    /**
     * Defines allowed CREATE keys
     *
     * @var array CREATE_KEYS
     */
    public const CREATE_KEYS = [];

    /**
     * Resource URL for a single item
     *
     * @var array CREATE_KEYS
     */
    public const ITEM_URL = '';

    /**
     * Resource URL for a multiple items operation or create
     *
     * @var array CREATE_KEYS
     */
    public const ITEMS_URL = '';
    /**
     * Stores the keys to be casted
     *
     * @var array $casts
     */
    protected $_casts = [];
    /**
     * Stores the data
     *
     * @var array $_data
     */
    protected $_data = array();
    /**
     * Stores the original data without any cast
     *
     * @var array $_original
     */
    protected $_original = array();

    /**
     * Stores the auth for automatic saves & updates
     *
     * @var Auth|null $_auth
     */
    private $_auth;

    /**
     * @param null|Auth $auth
     * @param null $data
     * @return AbstractEntity|static|$this
     */
    public static function make($auth = null, $data = null)
    {
        /** @var AbstractEntity|static|$this $object */
        $object = new static($data);
        if ($auth instanceof Auth) {
            $object->withAuth($auth);
        }
        return $object;
    }

    /**
     * Default Constructor
     *
     * You can pass data as a json representation or array object. This argument eliminates the need
     * to do $obj->fromJson($data) later after creating the object.
     *
     * @param array|string|null $data
     * @throws
     */
    public function __construct($data = null)
    {
        switch (gettype($data)) {
            case 'NULL':
                break;
            case 'string':
                JsonValidator::validate($data);
                $this->fromJson($data);
                break;
            case 'array':
                $this->fromArray($data);
                break;
            default:
        }
    }

    /**
     * Fills object value from Json string
     *
     * @param $json
     * @return $this
     * @throws Exception
     */
    public function fromJson($json)
    {
        return $this->fromArray(json_decode($json, true));
    }

    /**
     * Fills object value from Array list
     *
     * @param $arr
     * @return $this
     * @throws Exception
     */
    public function fromArray($arr)
    {
        if (null !== self::CONTAINER) {
            $arr = array_get($arr, self::CONTAINER);
        }

        if (!empty($arr)) {
            // Iterate over each element in array
            foreach ($arr as $k => $v) {
                // If the value is an array, it means, it is an object after conversion
                if (!is_array($v)) {
                    $this->assignValue($k, $v);
                }
                // Determine the class of the object

                if (($clazz = ReflectionUtil::getPropertyClass(get_class($this), $k)) !== null) {
                    // If the value is an associative array, it means, its an object. Just make recursive call to it.
                    if (empty($v)) {
                        if (ReflectionUtil::isPropertyClassArray(get_class($this), $k)) {
                            // It means, it is an array of objects.
                            $this->assignValue($k, array());
                            continue;
                        }

                        $o = new $clazz();
                        //$arr = array();
                        $this->assignValue($k, $o);
                    } elseif (is_array($v) && ArrayUtil::isAssocArray($v)) {
                        /** @var self $o */
                        $o = new $clazz();
                        $o->fromArray($v);
                        $this->assignValue($k, $o);
                    } elseif (is_array($v)) {
                        // Else, value is an array of object/data
                        $arr = array();
                        // Iterate through each element in that array.
                        foreach ($v as $nk => $nv) {
                            if (is_array($nv)) {
                                /** @var self $o */
                                $o = new $clazz();
                                $o->fromArray($nv);
                                $arr[$nk] = $o;
                            } else {
                                $arr[$nk] = $nv;
                            }
                        }
                        $this->assignValue($k, $arr);
                    } else {
                        $this->assignValue($k, $v);
                    }
                } else {
                    $this->assignValue($k, $v);
                }
            }
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     */
    private function assignValue($key, $value)
    {
        $setter = 'set' . $this->convertToCamelCase($key);
        // If we find the setter, use that, otherwise use magic method.
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $this->__set($key, $value);
        }
    }

    /**
     * Converts the input key into a valid Setter Method Name
     *
     * @param $key
     * @return mixed
     */
    private function convertToCamelCase($key)
    {
        return Str::camel($key);
        //return str_replace(' ', '', ucwords(str_replace(array('_', '-'), ' ', $key)));
    }

    /**
     * Returns a list of Object from Array or Json String. It is generally used when your json
     * contains an array of this object
     *
     * @param mixed $data Array object or json string representation
     * @return array|AbstractEntity
     * @throws Exception
     */
    public static function getList($data)
    {
        // Return Null if Null
        if ($data === null) {
            return null;
        }

        if (is_a($data, get_class(new stdClass()))) {
            //This means, root element is object
            return new static(json_encode($data));
        }

        $list = array();

        if (is_array($data)) {
            $data = json_encode($data);
        }

        if (JsonValidator::validate($data)) {
            // It is valid JSON
            $decoded = json_decode($data, false);
            if ($decoded === null) {
                return $list;
            }
            if (is_array($decoded)) {
                foreach ($decoded as $k => $v) {
                    $list[] = self::getList($v);
                }
            }
            if (is_a($decoded, get_class(new stdClass()))) {
                //This means, root element is object
                $list[] = new static(json_encode($decoded));
            }
        }

        return $list;
    }

    /**
     * Magic Get Method
     *
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->__isset($key)) {
            return $this->_data[$key];
        }
        return null;
    }

    /**
     * Magic Set Method
     *
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        if (!is_array($value) && $value === null) {
            $this->__unset($key);
        } else {
            if (isset($this->_casts[$key])) {
                $this->_data[$key] = CastUtil::cast($value, $this->_casts[$key]);
            } else {
                $this->_data[$key] = $value;
            }
        }
        $this->_original[$key] = $value;
    }

    /**
     * Magic isSet Method
     *
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->_data[$key]);
    }

    /**
     * Magic Unset Method
     *
     * @param $key
     */
    public function __unset($key)
    {
        unset($this->_data[$key]);
    }

    /**
     * Get the original Parameter without casting
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function getOriginal($key, $default = null)
    {
        return Arr::get($this->_original, $key, $default);
    }

    /**
     * Returns array representation of object with only the specified keys
     *
     * @param array $only
     * @return array
     * @throws
     */
    public function toArrayOnly($only = [])
    {
        return Arr::only($this->_convertToArray($this->_data), $only);
    }

    /**
     * Converts Params to Array
     *
     * @param $param
     * @return array
     * @throws Exception
     */
    private function _convertToArray($param)
    {
        $ret = array();
        foreach ($param as $k => $v) {
            if ($v instanceof self) {
                $ret[$k] = $v->toArray();
            } elseif (is_array($v) && count($v) <= 0) {
                $ret[$k] = array();
            } elseif (is_array($v)) {
                $ret[$k] = $this->_convertToArray($v);
            } else {
                $ret[$k] = $v;
            }
        }
        // If the array is empty, which means an empty object,
        // we need to convert array to StdClass object to properly
        // represent JSON String
        if (count($ret) <= 0) {
            $ret = new AbstractEntity();
        }
        return $ret;
    }

    /**
     * Returns array representation of object
     *
     * @return array
     * @throws
     */
    public function toArray()
    {
        return $this->_convertToArray($this->_data);
    }

    /**
     * Returns array representation of object with only the specified keys
     *
     * @param array $except
     * @return array
     * @throws
     */
    public function toArrayExcept($except = [])
    {
        return Arr::except($this->_convertToArray($this->_data), $except);
    }

    /**
     * Magic Method for toString
     *
     * @return string
     * @throws Exception
     */
    public function __toString()
    {
        return $this->toJSON(128);
    }

    /**
     * Returns object JSON representation
     *
     * @param int $options http://php.net/manual/en/json.constants.php
     * @return string
     * @throws Exception
     */
    public function toJSON($options = 0)
    {
        // Because of PHP Version 5.3, we cannot use JSON_UNESCAPED_SLASHES option
        // Instead we would use the str_replace command for now.
        if (PHP_VERSION_ID >= 50400 === true) {
            return json_encode($this->toArray(), $options | 64);
        }
        return str_replace('\\/', '/', json_encode($this->toArray(), $options));
    }

    /**
     * @param Auth $auth
     * @return $this
     */
    public function withAuth(Auth $auth)
    {
        $this->_auth = $auth;
        return $this;
    }

    /**
     * @return array|Auth
     */
    protected function getAuth()
    {
        return $this->_auth;
    }

    /**
     * Check if the entity is already loaded by checking if there is an ID present
     *
     * @return bool
     */
    public function isCreated()
    {
        return $this->id !== null && is_numeric($this->id);
    }

    /**
     * Save method, should only be used with parent::save() or implement some nice logic here to grab the API function
     * Since API and ORM are detached.
     *
     * @param null $auth
     * @return mixed
     * @throws InvalidAuth
     */
    public function save($auth = null)
    {
        if ($auth && $auth instanceof Auth) {
            $this->withAuth($auth);
        }

        if (!$this->getAuth()) {
            throw new InvalidAuth();
        }
        return null;
    }
}