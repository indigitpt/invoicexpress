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
 * @property Client[] items - Company name
 * @method Client[] getItems() - Method to get Clients collection
 */
class ClientsCollection extends AbstractEntityCollection
{
    /**
     * @param array $object
     * @return ClientsCollection
     */
    protected function setClients($object = [])
    {
        $loop = [];
        foreach ($object as $item) {
            if (!$item instanceof Client) {
                $loop[] = new Client($item);
            } else {
                $loop[] = $item;
            }
        }
        $this->items = $loop;
        return $this;
    }
}