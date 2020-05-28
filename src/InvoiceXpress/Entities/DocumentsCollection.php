<?php


namespace InvoiceXpress\Entities;

/**
 * Class DocumentsCollection
 *
 * Manage a collection of Documents: Invoices, Receipts, etc.
 *
 * @package InvoiceXpress\Entities
 *
 * @property Invoice[] items - Company name
 * @method Invoice[] getItems() - Method to get Clients collection
 */
class DocumentsCollection extends AbstractEntityCollection
{
    /**
     * @param array $object
     * @return DocumentsCollection
     */
    protected function setDocuments($object = [])
    {
        $loop = [];
        foreach ($object as $item) {
            if (!is_array($item)) {
                $loop[] = $item;
            } else {
                $type = array_get($item, 'type');
                if ($type !== null) {
                    switch ($type) {
                        case Invoice::DOCUMENT_TYPE_CAMEL_RECEIPT:
                            $loop[] = new Receipt($item);
                            break;
                        default:
                            $loop[] = new Invoice($item);
                            break;
                    }
                }
            }
        }
        $this->items = $loop;
        return $this;
    }
}