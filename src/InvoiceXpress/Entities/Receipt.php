<?php


namespace InvoiceXpress\Entities;

use InvoiceXpress\Auth;
use InvoiceXpress\Entities\Client as ClientEntity;
use InvoiceXpress\Exceptions\InvalidAuth;
use InvoiceXpress\Exceptions\InvalidResponse;
use InvoiceXpress\Exceptions\ObjectMissing;

/**
 * Class InvoiceItem
 *
 * Lets you create, process and manage clients.
 *
 * @package InvoiceXpress\Entities
 *
 * @property integer id - id of the item
 * @property string payment_mechanism - Payment Method, @see \InvoiceXpress\Constants::PAYMENT_METHODS
 * @property string note - Notes for the payment receipt
 * @property string serie - Document series object
 * @property integer|float amount - Amount of the payment
 * @property string payment_date - Date of the payment/receipt
 *
 * @property-read string status
 * @property-read boolean archived
 * @property-read string sequence_number
 * @property-read string inverted_sequence_number
 * @property-read string date - date of the receipt issued
 * @property-read string due_date - date of the invocie
 * @property-read string reference
 * @property-read string observations - Same as notes
 * @property-read integer retention - Same as invoice rentations
 * @property-read integer permalink - Link to the receipt
 * @property-read integer saft_hash - Hash for the saft
 * @property-read integer|float sum
 * @property-read integer|float discount
 * @property-read integer|float before_taxes
 * @property-read integer|float taxes
 * @property-read integer|float total
 * @property-read string currency
 * @property-read Client|array client
 * @property-read InvoiceItem|array items
 *
 * @property integer invoice_id - Relation to invoice_id, this property is not native, we will just add to quickly
 * @property Invoice invoice - Invoice object for saving
 */
class Receipt extends AbstractEntity
{
    public const ITEM_IDENTIFIER = 'receipt_id';
    public const ITEM_URL = '/receipts/{receipt_id}/{action}.json';
    public const ITEM_CREATE = '/documents/{document_id}/partial_payments.json';
    public const CONTAINER = 'receipt';
    public const CONTAINER_CREATE = 'partial_payment';

    public const UPDATE_KEYS = [];
    public const CREATE_KEYS = [
        'payment_mechanism',
        'note',
        'serie',
        'amount',
        'payment_date',
    ];

    public const CANCEL_KEYS = [
        'state',
        'message',
    ];

    protected $_casts = [
        'payment_mechanism' => 'string',
        'note' => 'string',
        'serie' => 'string',
        'amount' => 'integer',
        'payment_date' => 'to_date'
    ];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $payment_mechanism
     * @return Receipt
     */
    public function setPaymentMechanism($payment_mechanism): Receipt
    {
        $this->payment_mechanism = $payment_mechanism;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMechanism(): string
    {
        return $this->payment_mechanism;
    }

    /**
     * @param string $note
     * @return Receipt
     */
    public function setNote($note): Receipt
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * @param string $serie
     * @return Receipt
     */
    public function setSerie($serie): Receipt
    {
        $this->serie = $serie;
        return $this;
    }

    /**
     * @return string
     */
    public function getSerie(): string
    {
        return $this->serie;
    }

    /**
     * @param float|int $amount
     * @return Receipt
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return float|int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $payment_date
     * @return Receipt
     */
    public function setPaymentDate($payment_date): Receipt
    {
        $this->payment_date = $payment_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentDate(): string
    {
        return $this->payment_date;
    }

    /**
     * @param integer $id
     * @return Receipt
     */
    public function setInvoiceId($id): Receipt
    {
        $this->invoice_id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getInvoiceId(): string
    {
        return $this->invoice_id;
    }

    /**
     * @param Invoice $invoice
     * @return Receipt
     */
    public function setInvoice(Invoice $invoice): Receipt
    {
        $this->invoice = $invoice;
        if (null !== $invoice->getId()) {
            $this->setInvoiceId($invoice->getId());
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * @return string
     */
    public function getSequenceNumber(): string
    {
        return $this->sequence_number;
    }

    /**
     * @return string
     */
    public function getInvertedSequenceNumber(): string
    {
        return $this->inverted_sequence_number;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getDueDate(): string
    {
        return $this->due_date;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getObservations(): string
    {
        return $this->observations;
    }

    /**
     * @return int
     */
    public function getRetention(): int
    {
        return $this->retention;
    }

    /**
     * @return int
     */
    public function getPermalink(): int
    {
        return $this->permalink;
    }

    /**
     * @return int
     */
    public function getSaftHash(): int
    {
        return $this->saft_hash;
    }

    /**
     * @return float|int
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * @return float|int
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return float|int
     */
    public function getBeforeTaxes()
    {
        return $this->before_taxes;
    }

    /**
     * @return float|int
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * @return float|int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @return array|Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return array|InvoiceItem
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Append the items to the invoice with proper objects.
     *
     * @param array $items
     */
    protected function setItems($items)
    {
        $loop = [];
        foreach ($items as $item) {
            if (!$item instanceof InvoiceItem) {
                $loop[] = new InvoiceItem($item);
            } else {
                $loop[] = $item;
            }
        }
        $this->items = $loop;
    }

    /**
     * Append the items to the invoice with proper objects.
     *
     * @param array $object
     */
    protected function setClient($object)
    {
        if (!$object instanceof ClientEntity) {
            $this->client = new ClientEntity($object);
        } else {
            $this->client = $object;
        }
    }

    /**
     * @param null|Auth $auth
     * @return Invoice|mixed|null
     * @throws InvalidAuth
     * @throws InvalidResponse
     */
    public function save($auth = null)
    {
        parent::save($auth);
        if ($this->getInvoice() === null) {
            throw new ObjectMissing('invoice');
        }
        $object = \InvoiceXpress\Api\Invoice::receiptCreate($this->getAuth(), $this->getInvoice(), $this);
        $this->fromArray($object->toArray());
        return $this;
    }
}