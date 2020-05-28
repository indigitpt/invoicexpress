<?php


namespace InvoiceXpress\Exceptions;


use Exception;

class InvalidCredentials extends Generic
{
    protected $see = 'https://www.app.invoicexpress.com/users/api';

    /**
     * InvalidCredentials constructor.
     *
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('Sorry but your credentials are not set. Please visit %s to get it', $this->see),
            $code, $previous);
    }
}