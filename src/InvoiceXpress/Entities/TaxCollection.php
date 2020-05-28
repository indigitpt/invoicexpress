<?php


namespace InvoiceXpress\Entities;


/**
 * Class Account
 *
 * Lets you create, process and manage clients.
 *
 * @package InvoiceXpress\Entities
 *
 * @property string id
 * @property Tax[] items
 * @method Tax[] getItems() - Method to get Taxes collection
 */
class TaxCollection extends AbstractEntityCollection
{
    /**
     * @param array $object
     * @return TaxCollection
     */
    protected function setTaxes($object = [])
    {
        $loop = [];
        foreach ($object as $item) {
            if (!$item instanceof Tax) {
                $loop[] = new Tax($item);
            } else {
                $loop[] = $item;
            }
        }
        $this->items = $loop;
        return $this;
    }
}