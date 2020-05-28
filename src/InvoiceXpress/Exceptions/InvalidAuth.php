<?php


namespace InvoiceXpress\Exceptions;


use Exception;

class InvalidAuth extends Generic
{

    /**
     * InvalidAuth constructor.
     *
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($code = 0, Exception $previous = null)
    {
        parent::__construct('The auth object is missing please use withAuth to pass the authentication object into your entity',
            $code, $previous);
    }
}