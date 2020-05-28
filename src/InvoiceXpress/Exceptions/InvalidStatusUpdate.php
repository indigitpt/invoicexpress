<?php


namespace InvoiceXpress\Exceptions;


use Exception;

class InvalidStatusUpdate extends Generic
{
    protected $see = 'https://invoicexpress.com/new-api-v2/invoices/change-state';

    /**
     * InvalidStatusUpdate constructor.
     *
     * @param $current
     * @param array $from
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($current, $from = [], $code = 0, Exception $previous = null)
    {
        parent::__construct(sprintf('Document status is [%s] you are only allowed to update into : [%s]. See %s for more information',
            $current, implode(',', $from), $this->see), $code, $previous);
    }
}