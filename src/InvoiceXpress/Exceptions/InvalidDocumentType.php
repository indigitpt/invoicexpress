<?php


namespace InvoiceXpress\Exceptions;

use Exception;
use InvoiceXpress\Entities\Invoice;

class InvalidDocumentType extends Generic
{
    /**
     * InvalidDocumentType constructor.
     *
     * @param array $types
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($types = [], $code = 0, Exception $previous = null)
    {
        if (empty($types)) {
            $type = array_keys(Invoice::DOCUMENT_TYPES);
        }
        parent::__construct(sprintf('The document type is not valid. Valid Document Types are: %s',
            implode(', ', $types)), $code, $previous);
    }
}