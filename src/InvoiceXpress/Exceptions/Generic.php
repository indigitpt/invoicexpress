<?php


namespace InvoiceXpress\Exceptions;

use Exception;
use Throwable;

class Generic extends Exception
{
    protected $context = [];

    /**
     * Generic constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the context
     *
     * @param null $key
     * @return array|string|mixed
     */
    public function getContext($key = null)
    {
        if (isset($this->context[$key]) && $this->context[$key] !== null) {
            return $this->context[$key];
        }
        return $this->context;
    }

    /**
     * Add items to context
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function addContext($key, $value)
    {
        $this->context[$key] = $value;
        return $this;
    }
}