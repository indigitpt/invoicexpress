<?php


namespace InvoiceXpress\Exceptions;


use Exception;

class WaitingPDF extends Generic
{

    /**
     * WaitingPDF constructor.
     *
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($code = 202, Exception $previous = null)
    {
        parent::__construct('The PDF is being generated, please check back later', $code, $previous);
    }
}