<?php


namespace InvoiceXpress\Exceptions;


use Exception;

class ObjectMissing extends Generic
{

    /**
     * ObjectMissing constructor.
     *
     * @param string $object
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($object = '', $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('The object of type : %s is required to continue with saving/updating', $object),
            $code, $previous);
    }
}