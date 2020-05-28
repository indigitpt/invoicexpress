<?php


namespace InvoiceXpress\Exceptions;

use Exception;

class ResourceNotFound extends Generic
{
    /**
     * ResourceNotFound constructor.
     *
     * @param string $type
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($type = '', $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('The resource of type [%s] was not found.', $type), $code, $previous);
    }
}