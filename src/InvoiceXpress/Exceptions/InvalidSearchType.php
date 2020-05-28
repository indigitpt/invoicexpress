<?php


namespace InvoiceXpress\Exceptions;


use Exception;

class InvalidSearchType extends Generic
{
    /**
     * InvalidSearchType constructor.
     *
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('Sorry but the search type is not available. Only search by name and code are available.'),
            $code, $previous);
    }
}