<?php


namespace InvoiceXpress\Api;

use InvoiceXpress\Entities\InvoiceItem as Entity;
use InvoiceXpress\Traits\ApiResource;

class InvoiceItem
{
    use ApiResource;

    public const ENTITY = Entity::class;
}