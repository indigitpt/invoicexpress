<?php


namespace InvoiceXpress\Entities;

/**
 * Class InvoiceItem
 *
 * Lets you create, process and manage clients.
 *
 * @package InvoiceXpress\Entities
 *
 * @property integer id - id of the item
 * @property string name - Name of the item : Ex Coca-Cola 33CL
 * @property string description - Description of the item : Ex: Fresh coca-cola from heaven
 * @property integer|float unit_price - Price for this item
 * @property integer quantity - Quantity Sold - TODO : Check here if we set 10x Times quantity, if the value is multiplied automaticly.
 * @property integer unit - Unite type @see Constants::UNIT_TYPES
 * @property integer|float discount - Discount to be applied if any, otherwise 0
 * @property array|Tax[] tax - Object of type TAX, but here the documentation says we only need the NAME, but we arent sure yet. Lets figure out later. @see InvoiceXpress\Entities|TaxTypes. So {"tax": {"name": "IVA23"}}
 */
class InvoiceItem extends AbstractEntity
{
    public const ITEM_IDENTIFIER = 'item_id';
    public const ITEM_URL = '/items/{item_id}.json';
    public const ITEMS_URL = '/items.json';
    public const CONTAINER = 'item';
    public const UPDATE_KEYS = [
        'name',
        'description',
        'unit_price',
        'unit',
        'tax',
    ];
    public const CREATE_KEYS = [
        'name',
        'description',
        'unit_price',
        'unit',
        'tax',
    ];
    public const UNIT_TYPE_UNIT = 'unit';

    # Invoices unit types, this in fact could be any string, but we will use "Any" as "other"
    public const UNIT_TYPE_SERVICE = 'service';
    public const UNIT_TYPE_HOUR = 'hour';
    public const UNIT_TYPE_DAY = 'day';
    public const UNIT_TYPE_MONTH = 'month';
    public const UNIT_TYPE_OTHER = 'other';
    public const UNIT_TYPES = [
        self::UNIT_TYPE_SERVICE => self::UNIT_TYPE_SERVICE,
        self::UNIT_TYPE_UNIT => self::UNIT_TYPE_UNIT,
        self::UNIT_TYPE_HOUR => self::UNIT_TYPE_HOUR,
        self::UNIT_TYPE_DAY => self::UNIT_TYPE_DAY,
        self::UNIT_TYPE_MONTH => self::UNIT_TYPE_MONTH,
        self::UNIT_TYPE_OTHER => self::UNIT_TYPE_OTHER
    ];
    protected $_casts = [
        'name' => 'string',
        'description' => 'string',
        'unit_price' => 'string',
        'unit' => 'string',
        'quantity' => 'string'
    ];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return InvoiceItem
     */
    public function setName($name): InvoiceItem
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $description
     * @return InvoiceItem
     */
    public function setDescription($description): InvoiceItem
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param float|int $unit_price
     * @return InvoiceItem
     */
    public function setUnitPrice($unit_price)
    {
        $this->unit_price = $unit_price;
        return $this;
    }

    /**
     * @return float|int
     */
    public function getUnitPrice()
    {
        return $this->unit_price;
    }

    /**
     * @param int $quantity
     * @return InvoiceItem
     */
    public function setQuantity($quantity): InvoiceItem
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $unit
     * @return InvoiceItem
     */
    public function setUnit($unit): InvoiceItem
    {
        $this->unit = $unit;
        return $this;
    }

    /**
     * @return int
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param float|int $discount
     * @return InvoiceItem
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * @return float|int
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return array|Tax[]
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param array|Tax $object
     * @param bool $only
     */
    public function setTax($object, $only = true)
    {
        if (!$object instanceof Tax) {
            $tax = new Tax($object);
        } else {
            $tax = $object;
        }
        $this->tax = $only ? $tax->toArrayOnly(Tax::ITEMS_KEYS) : $tax->toArray();
        return $this;
    }

}