<?php


namespace InvoiceXpress\Entities;

use Currency\CurrencyCode;
use InvoiceXpress\Auth;
use InvoiceXpress\Entities\Client as ClientEntity;
use InvoiceXpress\Exceptions\InvalidAuth;
use InvoiceXpress\Exceptions\InvalidDocumentType;
use InvoiceXpress\Exceptions\InvalidResponse;
use InvoiceXpress\Exceptions\InvalidStatusUpdate;
use InvoiceXpress\Exceptions\WaitingPDF;


/**
 * Class Account
 *
 * Lets you create, process and manage clients.
 *
 * @package InvoiceXpress\Entities
 *
 * @property string|integer id - The ID of the invoice
 * @property string date - Date of the invoice
 * @property string due_date - Date of due of this invoice
 * @property string reference - Reference, this should be also our order ID identifier with some hash or something that makes it unique.
 * @property string observations - Text field to add additional observations, this will also be visible on the invoice
 * @property integer retention - Tax Percentage i believe between 0 and 99.99.
 * @property integer sequence_id - ID to be used for sequencing this invocie ( Ex: M-xxx ) if empty default will be used NOT required
 * @property integer manual_sequence_number - Manual sequencing number, not used in Portugal so GTFO implement yourself foreign! :D
 * @property ClientEntity client - Client object, new or pass an object that already exists
 * @property InvoiceItem[] items - Items of the invoice
 * @property integer mb_reference - Toggle 0/1 to generate Multibanco Reference for the user pay. Feature must be enable and only valid for Portuguese Accounts. This field is also used when reading the object and contains the MB references
 * @property array mb_reference_details - We will set this field magic when MB reference details are sent
 * @property integer owner_invoice_id - Foreign key for the invoice only used for credit_notes or debit_notes types
 * @property string tax_exemption - Reason if the user is exempt from being taxed @see Tax::TAX_EXEMPTION_REASONS
 * @property string tax_exemption_reason - Im not sure whats the difference between this option and tax_exemption, one should be the reason and other should define the tax exemption, we need clear light here pls.
 * @property string currency_code - ISO currency code, we use the package provider @see https://github.com/fortis/iso-currency Ex: USD,EUR
 * @property float rate - If specifying a currency code, we can also specify the rate of the converstion.
 * @property string type_internal - Used to set the document type, so we can automaticly crawl the necessary stuff without passing additional parameters to functions
 *
 * # Keys Available on GET Invoice
 * @property-read string status - The status of invoice, the list of statuses are bellow
 * @property-read boolean archived - If the invoice is archived or not
 * @property-read string type - The type of the invoice, receipt etc with CAMEL case
 * @property-read string sequence_number - Sequence Number ( Ex : 6/G )
 * @property-read string inverted_sequence_number - Inverted Sequence Number ( Ex: G/6 )
 * @property-read string permalink - Permanent Link to the invoice/document
 * @property-read string saft_hash - Hash of the cotents of the invoice for saft proposes.
 * @property-read float sum - Total Amount of the invoice without Taxes
 * @property-read float discount - Discount applied on the invoice
 * @property-read float before_taxes - Amount of the invoice BEFORE taxes
 * @property-read float taxes - Amount of money in Taxes
 * @property-read float total - Total Amount of the invoice
 *
 */
class Invoice extends AbstractEntity
{

    public const CONTAINER = 'invoice';
    public const CONTAINER_DOCUMENTS = 'documents';
    public const ITEM_URL = '/{document_type}/{document_id}.json';
    public const ITEM_ACTION = '/{document_type}/{document_id}/{action}.json';
    public const ITEM_CREATE = '/{document_type}.json';
    public const ITEMS_URL = '/invoices.json';
    public const ITEM_PDF = '/api/pdf/{document_id}.json';
    public const ITEM_PDF_DOWNLOAD = '/pdf/start/{document_id}/{second_copy}'; # Unable to get via API, lets try here
    public const ITEM_DOCUMENTS = '/document/{document_id}/{action}.json';

    public const ITEM_IDENTIFIER = 'document_id';
    public const ITEM_TYPE_IDENTIFIER = 'document_type';
    public const CREATE_KEYS = [
        'date',
        'due_date',
        'reference',
        'observations',
        'retention',
        'tax_exemption',
        'sequence_id',
        'manual_sequence_number',
        'client',
        'items',
        'mb_reference',
        'owner_invoice_id',
        'tax_exemption_reason',
        'currency_code',
        'rate'
    ];

    # Document Type Names
    public const DOCUMENT_TYPE_INVOICE = 'invoices';
    public const DOCUMENT_TYPE_INVOICE_SIMPLIFIED = 'simplified_invoices';
    public const DOCUMENT_TYPE_INVOICE_RECEIPTS = 'invoice_receipts';
    public const DOCUMENT_TYPE_VAT_MOSS_INVOICE = 'vat_moss_invoices';
    public const DOCUMENT_TYPE_CREDIT_NOTES = 'credit_notes';
    public const DOCUMENT_TYPE_DEBIT_NOTES = 'debit_notes';
    public const DOCUMENT_TYPE_RECEIPT = 'receipts';

    public const DOCUMENT_TYPES = [
        self::DOCUMENT_TYPE_INVOICE => self::DOCUMENT_TYPE_INVOICE,
        self::DOCUMENT_TYPE_INVOICE_SIMPLIFIED => self::DOCUMENT_TYPE_INVOICE_SIMPLIFIED, # Only Available in Portugal
        self::DOCUMENT_TYPE_INVOICE_RECEIPTS => self::DOCUMENT_TYPE_INVOICE_RECEIPTS,
        self::DOCUMENT_TYPE_VAT_MOSS_INVOICE => self::DOCUMENT_TYPE_VAT_MOSS_INVOICE,
        self::DOCUMENT_TYPE_CREDIT_NOTES => self::DOCUMENT_TYPE_CREDIT_NOTES,
        self::DOCUMENT_TYPE_DEBIT_NOTES => self::DOCUMENT_TYPE_DEBIT_NOTES,
        self::DOCUMENT_TYPE_RECEIPT => self::DOCUMENT_TYPE_RECEIPT,
    ];
    public const DOCUMENT_TYPES_SINGULAR = [
        self::DOCUMENT_TYPE_INVOICE => 'invoice',
        self::DOCUMENT_TYPE_INVOICE_SIMPLIFIED => 'simplified_invoice', # Only Available in Portugal
        self::DOCUMENT_TYPE_INVOICE_RECEIPTS => 'invoice_receipt',
        self::DOCUMENT_TYPE_VAT_MOSS_INVOICE => 'vat_moss_invoice',
        self::DOCUMENT_TYPE_CREDIT_NOTES => 'credit_note',
        self::DOCUMENT_TYPE_DEBIT_NOTES => 'debit_note',
        self::DOCUMENT_TYPE_RECEIPT => 'receipt',
    ];

    # Converting plurar into singular, just to get the correct container
    public const DOCUMENT_TYPE_CAMEL_INVOICE = 'Invoice';
    public const DOCUMENT_TYPE_CAMEL_CASH_INVOICE = 'CashInvoice'; # Normal Invoice
    public const DOCUMENT_TYPE_CAMEL_DEBIT_NOTE = 'DebitNote'; # Invoice Paid with Real Moneyzzzz
    public const DOCUMENT_TYPE_CAMEL_INVOICE_RECEIPT = 'InvoiceReceipt'; # Note of Debit, no one cares about this tbh
    public const DOCUMENT_TYPE_CAMEL_VAT_MOSS = 'VatMossInvoice'; # Invoice with Receipt, must used in our case, when the user pays with Paypal or instant collect payments
    public const DOCUMENT_TYPE_CAMEL_SIMPLIFIED_INVOICE = 'SimplifiedInvoice'; # Moss for life yeah
    public const DOCUMENT_TYPE_CAMEL_CREDIT_NOTE = 'CreditNote'; # Simple invoice, this can only be used until 1000€ maximum i believe :(
    public const DOCUMENT_TYPE_CAMEL_VAT_MOSS_CREDIT_NOTE = 'VatMossCreditNote'; # When we make real mistakes, we need to issue credit notes, we robbed the costumer :)
    public const DOCUMENT_TYPE_CAMEL_RECEIPT = 'Receipt'; # Yeah, same but vat moss thing
    public const DOCUMENT_TYPE_CAMEL_VAT_MOSS_RECEIPT = 'VatMossReceipt'; # Only used when we first issued a INVOICE only, after invoice the next step is receipt

    # Camel to Singular conversion
    public const DOCUMENT_TYPES_CAMEL = [
        self::DOCUMENT_TYPE_CAMEL_INVOICE => self::DOCUMENT_TYPE_INVOICE,
        self::DOCUMENT_TYPE_CAMEL_INVOICE_RECEIPT => self::DOCUMENT_TYPE_INVOICE_RECEIPTS,
        self::DOCUMENT_TYPE_CAMEL_DEBIT_NOTE => self::DOCUMENT_TYPE_DEBIT_NOTES,
        self::DOCUMENT_TYPE_CAMEL_VAT_MOSS => self::DOCUMENT_TYPE_VAT_MOSS_INVOICE,
        self::DOCUMENT_TYPE_CAMEL_SIMPLIFIED_INVOICE => self::DOCUMENT_TYPE_INVOICE_SIMPLIFIED,
        self::DOCUMENT_TYPE_CAMEL_CREDIT_NOTE => self::DOCUMENT_TYPE_CREDIT_NOTES,
        self::DOCUMENT_TYPE_CAMEL_RECEIPT => self::DOCUMENT_TYPE_RECEIPT,
    ];

    # Invoices filters for his STATUS Of Archived
    public const INVOICE_FILTERS_NON_ARCHIVED_DOCUMENT = 'non_archived';
    public const INVOICE_FILTERS_ARCHIVED_DOCUMENT = 'archived'; # Vat Moss thing

    # Status of the documents
    public const DOCUMENT_STATUS_DRAFT = 'draft';
    public const DOCUMENT_STATUS_SENT = 'sent';
    public const DOCUMENT_STATUS_FINAL = 'final'; # Also known as sent
    public const DOCUMENT_STATUS_SETTLED = 'settled'; # The documentation says its final, but the website whilte inspecting shows as "sent". in Portuguese also as "paid"
    public const DOCUMENT_STATUS_CANCELED = 'canceled'; # Also known as paid
    public const DOCUMENT_STATUS_SECOND_COPY = 'second_copy'; # Also known as canceled
    public const DOCUMENT_STATUS_DELETED = 'deleted'; # Also known as second copy of document

    # Used for status update, another hammer here :(
    public const DOCUMENT_STATUS_CHANGE_FINAL = 'finalized';
    public const DOCUMENT_STATUS_CHANGE_DELETED = 'deleted';
    public const DOCUMENT_STATUS_CHANGE_CANCEL = 'canceled';
    public const DOCUMENT_STATUS_CHANGE_SETTLED = 'settled'; # Paid/Final, not sure what the difference
    public const DOCUMENT_STATUS_CHANGE_UNSETTLED = 'unsettled';


    public const DOCUMENT_STATUSES = [
        self::DOCUMENT_STATUS_DRAFT => self::DOCUMENT_STATUS_DRAFT,
        self::DOCUMENT_STATUS_SENT => self::DOCUMENT_STATUS_SENT,
        self::DOCUMENT_STATUS_FINAL => self::DOCUMENT_STATUS_FINAL,
        self::DOCUMENT_STATUS_SETTLED => self::DOCUMENT_STATUS_SETTLED,
        self::DOCUMENT_STATUS_CANCELED => self::DOCUMENT_STATUS_CANCELED,
        self::DOCUMENT_STATUS_SECOND_COPY => self::DOCUMENT_STATUS_SECOND_COPY,
    ];

    # Draft to Deleted is possible
    public const STATUS_CHANGES = [
        self::DOCUMENT_STATUS_DRAFT => [
            self::DOCUMENT_STATUS_FINAL,
            self::DOCUMENT_STATUS_SETTLED,
            self::DOCUMENT_STATUS_DELETED,
        ],
        self::DOCUMENT_STATUS_FINAL => [
            self::DOCUMENT_STATUS_CANCELED,
            self::DOCUMENT_STATUS_SETTLED
        ],
        self::DOCUMENT_STATUS_SETTLED => [
            self::DOCUMENT_STATUS_CANCELED,
            self::DOCUMENT_STATUS_FINAL
        ]
    ];

    # Status changes allowance
    # Defines what kind of status can be updated to another status
    public $_casts = [
        'date' => 'to_date',
        'due_date' => 'to_date',
        'reference' => 'string',
        'observations' => 'string',
        'retention' => 'string',
        'mb_reference' => 'string',
        'owner_invoice_id' => 'string',
        'tax_exemption' => 'string',
        'tax_exemption_reason' => 'string',
        'rate' => 'string'
    ];

    /**
     * Add a Invoice item to the stack
     *
     * @param InvoiceItem $item
     * @return $this
     */
    public function addItem(InvoiceItem $item)
    {
        $current = $this->items ?? [];
        if (!is_array($current)) {
            $current = [];
            $current[] = $item;
        } else {
            $current[] = $item;
        }
        $this->items = $current;
        return $this;
    }

    /**
     * Clears all line items in the invoices
     *
     * @return $this
     */
    public function clearItems()
    {
        $this->items = [];
        return $this;
    }

    /**
     * Set the client to the invoice
     *
     * @param Client $client
     * @return $this
     */
    public function addClient(ClientEntity $client)
    {
        $this->client = $client->toArrayOnly(ClientEntity::INVOICE_KEYS);
        return $this;
    }

    /**
     * @param int|string $id
     * @return Invoice
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $date
     * @return Invoice
     */
    public function setDate($date): Invoice
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $due_date
     * @return Invoice
     */
    public function setDueDate($due_date): Invoice
    {
        $this->due_date = $due_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getDueDate()
    {
        return $this->due_date;
    }

    /**
     * @param string $reference
     * @return Invoice
     */
    public function setReference($reference): Invoice
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $observations
     * @return Invoice
     */
    public function setObservations($observations): Invoice
    {
        $this->observations = $observations;
        return $this;
    }

    /**
     * @return string
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * @return InvoiceItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @param int $retention
     * @return Invoice
     */
    public function setRetention($retention): Invoice
    {
        $this->retention = $retention;
        return $this;
    }

    /**
     * @return int
     */
    public function getRetention()
    {
        return $this->retention;
    }

    /**
     * @param string $tax_exemption
     * @return Invoice
     */
    public function setTaxExemption($tax_exemption): Invoice
    {
        $this->tax_exemption = $tax_exemption;
        return $this;
    }

    /**
     * @return string
     */
    public function getTaxExemption()
    {
        return $this->tax_exemption;
    }

    /**
     * @param int $sequence_id
     * @return Invoice
     */
    public function setSequenceId($sequence_id): Invoice
    {
        $this->sequence_id = $sequence_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getSequenceId()
    {
        return $this->sequence_id;
    }

    /**
     * @param int $manual_sequence_number
     * @return Invoice
     */
    public function setManualSequenceNumber($manual_sequence_number): Invoice
    {
        $this->manual_sequence_number = $manual_sequence_number;
        return $this;
    }

    /**
     * @return int
     */
    public function getManualSequenceNumber()
    {
        return $this->manual_sequence_number;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Invoice
     */
    public function setMbReferencesEnable(): Invoice
    {
        $this->mb_reference = true;
        return $this;
    }

    /**
     * @return Invoice
     */
    public function setMbReferencesDisable(): Invoice
    {
        $this->mb_reference = false;
        return $this;
    }

    /**
     * @return int
     */
    public function getMbReference()
    {
        return $this->mb_reference;
    }

    /**
     * @return array
     */
    public function getMbReferenceDetails(): array
    {
        return $this->mb_reference_details;
    }

    /**
     * @param int $owner_invoice_id
     * @return Invoice
     */
    public function setOwnerInvoiceId($owner_invoice_id): Invoice
    {
        $this->owner_invoice_id = $owner_invoice_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getOwnerInvoiceId()
    {
        return $this->owner_invoice_id;
    }

    /**
     * @param string $tax_exemption_reason
     * @return Invoice
     */
    public function setTaxExemptionReason($tax_exemption_reason): Invoice
    {
        $this->tax_exemption_reason = $tax_exemption_reason;
        return $this;
    }

    /**
     * @return string
     */
    public function getTaxExemptionReason()
    {
        return $this->tax_exemption_reason;
    }

    /**
     * @param string $currency_code
     * @return Invoice
     */
    public function setCurrencyCode($currency_code = CurrencyCode::EUR): Invoice
    {
        $this->currency_code = $currency_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currency_code;
    }

    /**
     * @param float $rate
     * @return Invoice
     */
    public function setRate(float $rate): Invoice
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * @return float
     */
    public function getRate(): float
    {
        return $this->rate;
    }

    /**
     * Get the internal type
     *
     * @return string
     */
    public function getTypeInternal()
    {
        return $this->type_internal;
    }

    /**
     * Set the type to the desire internal type
     *
     * @param $type
     * @return self
     */
    public function setTypeInternal($type)
    {
        $this->type_internal = $type;
        return $this;
    }

    /**
     * Set the docuemnt type to "Invoice"
     *
     * @return $this
     */
    public function typeInvoice()
    {
        $this->type_internal = self::DOCUMENT_TYPE_INVOICE;
        return $this;
    }

    /**
     * Set the docuemnt type to "Invoice+Receipt"
     *
     * @return $this
     */
    public function typeInvoiceReceipt()
    {
        $this->type_internal = self::DOCUMENT_TYPE_INVOICE_RECEIPTS;
        return $this;
    }

    /**
     * Set the docuemnt type to "Simplified Invoice"
     *
     * @return $this
     */
    public function typeInvoiceSimplified()
    {
        $this->type_internal = self::DOCUMENT_TYPE_INVOICE_SIMPLIFIED;
        return $this;
    }

    /**
     * Set the docuemnt type to "Simplified Invoice"
     *
     * @return $this
     */
    public function typeInvoiceVatMoss()
    {
        $this->type_internal = self::DOCUMENT_TYPE_VAT_MOSS_INVOICE;
        return $this;
    }

    /**
     * Set the docuemnt type to "Credit Note"
     *
     * @return $this
     */
    public function typeInvoiceCreditNote()
    {
        $this->type_internal = self::DOCUMENT_TYPE_CREDIT_NOTES;
        return $this;
    }

    /**
     * Set the docuemnt type to "Debit Note"
     *
     * @return $this
     */
    public function typeInvoiceDebitNote()
    {
        $this->type_internal = self::DOCUMENT_TYPE_DEBIT_NOTES;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getSequenceNumber()
    {
        return $this->sequence_number;
    }

    /**
     * @return string
     */
    public function getInvertedSequenceNumber()
    {
        return $this->inverted_sequence_number;
    }

    /**
     * @return string
     */
    public function getPermalink()
    {
        return $this->permalink;
    }

    /**
     * @return string
     */
    public function getSaftHash()
    {
        return $this->saft_hash;
    }

    /**
     * @return float
     */
    public function getSum(): float
    {
        return $this->sum;
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @return float
     */
    public function getBeforeTaxes(): float
    {
        return $this->before_taxes;
    }

    /**
     * @return float
     */
    public function getTaxes(): float
    {
        return $this->taxes;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
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
     * Because the same field is used for setting and different format for reading.
     * This API is full of hammered code afaik :)
     *
     * @param $object
     */
    protected function setMbReference($object)
    {
        if (is_array($object)) {
            $this->mb_reference_details = $object;
            $this->mb_reference = true;
        }
    }

    /**
     * Check if the document type is qualified for creating receipts
     *
     * @return bool
     */
    public function canCreateReceipt()
    {
        return in_array($this->getTypeToPlural(),
            [self::DOCUMENT_TYPE_INVOICE, self::DOCUMENT_TYPE_INVOICE_SIMPLIFIED, self::DOCUMENT_TYPE_VAT_MOSS_INVOICE],
            true);
    }

    /**
     * @return mixed|string
     */
    public function getTypeToSingular()
    {
        # We assume the default type is always camel on the invoice object
        # Lets do the correct conversions here.
        # I wish the did pretty more standard naming conventions
        $plural = self::DOCUMENT_TYPES_CAMEL[$this->type];
        return self::DOCUMENT_TYPES_SINGULAR[$plural];
    }

    /**
     * @return mixed|string
     */
    public function getTypeToPlural()
    {
        return self::DOCUMENT_TYPES_CAMEL[$this->type];
    }

    /**
     * @param $singular
     * @return string
     */
    public static function typeFromPluralToSingular($singular)
    {
        return array_get(self::DOCUMENT_TYPES_SINGULAR, $singular);
    }

    /**
     * @param $singular
     * @return string
     */
    public static function typeFromSingularToPlural($singular)
    {
        # lolwoot hax
        return $singular . 's';
    }

    /**
     * @param $camel
     * @return mixed|string
     */
    public static function typeFromCamelToSingular($camel)
    {
        $plural = self::DOCUMENT_TYPES_CAMEL[$camel];
        return self::DOCUMENT_TYPES_SINGULAR[$plural];
    }

    /**
     * @param $camel
     * @return mixed|string
     */
    public static function typeFromCamelToPlural($camel)
    {
        return self::DOCUMENT_TYPES_CAMEL[$camel];
    }


    /**
     * @param $second_copy
     * @return mixed
     * @throws InvalidAuth
     * @throws InvalidResponse
     * @throws WaitingPDF
     */
    public function getPDF($second_copy)
    {
        if (!$this->getAuth()) {
            throw new InvalidAuth();
        }
        return \InvoiceXpress\Api\Invoice::pdfAlternative($this->getAuth(), $this->getId(), $second_copy);
    }

    /**
     * @param Receipt $receipt
     * @return Receipt
     * @throws InvalidAuth
     * @throws InvalidResponse
     * @throws InvalidDocumentType
     */
    public function createReceipt(Receipt $receipt)
    {
        if (!$this->getAuth()) {
            throw new InvalidAuth();
        }
        return \InvoiceXpress\Api\Invoice::receiptCreate($this->getAuth(), $this, $receipt);
    }

    /**
     * @param integer|string $id
     * @param $message
     * @return Receipt
     * @throws InvalidAuth
     * @throws InvalidResponse
     */
    public function cancelReceipt($id, $message)
    {
        if (!$this->getAuth()) {
            throw new InvalidAuth();
        }
        return \InvoiceXpress\Api\Invoice::receiptCancel($this->getAuth(), $id, $message);
    }

    /**
     * @param Email $email
     * @return bool
     * @throws InvalidAuth
     */
    public function sendEmail(Email $email)
    {
        if (!$this->getAuth()) {
            throw new InvalidAuth();
        }
        return \InvoiceXpress\Api\Invoice::emailSend($this->getAuth(), $this, $email);
    }

    /**
     * @return DocumentsCollection
     * @throws InvalidAuth
     * @throws InvalidResponse
     */
    public function relatedDocuments()
    {
        if (!$this->getAuth()) {
            throw new InvalidAuth();
        }
        return \InvoiceXpress\Api\Invoice::relatedDocuments($this->getAuth(), $this->getId());
    }

    /**
     * Shortcut for exemption reason for Europe
     *
     * @return $this
     */
    public function exportingToEurope()
    {
        $this->setTaxExemption(Tax::TAX_EXEMPTION_M16);
        $this->setTaxExemptionReason('Exporting to Europe');
        $this->removeVatFromAllItems();
        return $this;
    }

    /**
     * Shortcut for exemption reason for the rest of the world
     *
     * @return $this
     */
    public function exportingToWorld()
    {
        $this->setTaxExemption(Tax::TAX_EXEMPTION_M05);
        $this->setTaxExemptionReason('Exporting to Rest of the World');
        $this->removeVatFromAllItems();
        return $this;
    }

    /**
     * Removes all the VAT from the current items in the stack
     */
    private function removeVatFromAllItems()
    {
        # Make sure there is a zero tax object
        $zeroTax = new Tax();
        $zeroTax->setName('TAX0');
        $zeroTax->setValue(0);
        $zeroTax->setDefaultTax(false);
        $zeroTax->setRegion(Tax::TAX_REGION_PORTUGAL_UNKNOWN); # Unclear here if we should set any region

        if (!empty($this->getItems())) {
            $original = $this->getItems();
            # Clear all the current items
            $this->clearItems();
            foreach ($original as $item) {
                $item->setTax($zeroTax);
                // Add the Item back into the stack
                $this->addItem($item);
            }
        }
    }

    /**
     * @return Invoice
     * @throws InvalidDocumentType
     * @throws InvalidResponse
     * @throws InvalidStatusUpdate
     */
    public function statusFinal()
    {
        $status = $this->stateUpdateChecker(self::DOCUMENT_STATUS_FINAL);
        return \InvoiceXpress\Api\Invoice::state($this->getAuth(), $this, $status);
    }

    /**
     * @return Invoice
     * @throws InvalidDocumentType
     * @throws InvalidResponse
     * @throws InvalidStatusUpdate
     */
    public function statusDelete()
    {
        $status = $this->stateUpdateChecker(self::DOCUMENT_STATUS_DELETED);
        return \InvoiceXpress\Api\Invoice::state($this->getAuth(), $this, $status);
    }

    /**
     * @return Invoice
     * @throws InvalidDocumentType
     * @throws InvalidResponse
     * @throws InvalidStatusUpdate
     */
    public function statusSettled()
    {
        $status = $this->stateUpdateChecker(self::DOCUMENT_STATUS_SETTLED);
        return \InvoiceXpress\Api\Invoice::state($this->getAuth(), $this, $status);
    }

    /**
     * @param $reason
     * @return Invoice
     * @throws InvalidDocumentType
     * @throws InvalidResponse
     * @throws InvalidStatusUpdate
     */
    public function statusCancel($reason)
    {
        $status = $this->stateUpdateChecker(self::DOCUMENT_STATUS_CANCELED);
        return \InvoiceXpress\Api\Invoice::state($this->getAuth(), $this, $status, $reason);
    }

    /**
     * @param $to
     * @return string|null
     * @throws InvalidDocumentType
     * @throws InvalidStatusUpdate
     */
    private function stateUpdateChecker($to)
    {

        $statusTranslated = null;
        switch ($this->getStatus()) {
            # CURRENT STATUS IS DRAFT
            case self::DOCUMENT_STATUS_DRAFT:
                # Only invoice_receipts are allowed
                if ($to === self::DOCUMENT_STATUS_SETTLED && $this->getType() !== self::DOCUMENT_TYPE_CAMEL_INVOICE_RECEIPT) {
                    throw new InvalidDocumentType([self::DOCUMENT_TYPE_INVOICE_RECEIPTS]);
                }
                # Allowed statuses from Draft to -> xxxx
                $draftAllowed = [
                    self::DOCUMENT_STATUS_FINAL, self::DOCUMENT_STATUS_SETTLED, self::DOCUMENT_STATUS_DELETED
                ];
                if (!in_array($to, $draftAllowed, false)) {
                    throw new InvalidStatusUpdate($this->getStatus(), $draftAllowed);
                }

                # From Draft to Final or Settled
                if ($to === self::DOCUMENT_STATUS_FINAL || $to === self::DOCUMENT_STATUS_SETTLED) {
                    $statusTranslated = self::DOCUMENT_STATUS_CHANGE_FINAL;
                }
                # From Draft to Deleted
                if ($to === self::DOCUMENT_STATUS_DELETED) {
                    $statusTranslated = self::DOCUMENT_STATUS_CHANGE_DELETED;
                }
                break;

            # CURRENT STATUS IS FINAL
            case self::DOCUMENT_STATUS_FINAL:
                $finalAllowed = [self::DOCUMENT_STATUS_CANCELED, self::DOCUMENT_STATUS_SETTLED];
                if (!in_array($to, $finalAllowed, false)) {
                    throw new InvalidStatusUpdate($this->getStatus(), $finalAllowed);
                }

                # From Final to Canceled
                if ($to === self::DOCUMENT_STATUS_CANCELED) {
                    $statusTranslated = self::DOCUMENT_STATUS_CHANGE_CANCEL;
                }
                # From Final to Settled
                if ($to === self::DOCUMENT_STATUS_SETTLED) {
                    $statusTranslated = self::DOCUMENT_STATUS_CHANGE_SETTLED;
                }
                break;


            # CURRENT STATUS IS SETTLED
            case self::DOCUMENT_STATUS_SETTLED:
                $settledAllowed = [self::DOCUMENT_STATUS_CANCELED, self::DOCUMENT_STATUS_FINAL];
                if (!in_array($to, $settledAllowed, false)) {
                    throw new InvalidStatusUpdate($this->getStatus(), $settledAllowed);
                }

                # From Settled to Canceled
                if ($to === self::DOCUMENT_STATUS_CANCELED) {
                    # Only for invoice_receipt
                    if ($this->getType() !== self::DOCUMENT_TYPE_CAMEL_INVOICE_RECEIPT) {
                        throw new InvalidDocumentType([self::DOCUMENT_TYPE_INVOICE_RECEIPTS]);
                    }
                    $statusTranslated = self::DOCUMENT_STATUS_CHANGE_CANCEL;
                }
                # From Settled to Final
                if ($to === self::DOCUMENT_STATUS_SETTLED) {
                    # Only credit_note & debit_note
                    if (!in_array($this->getType(),
                        [self::DOCUMENT_TYPE_CAMEL_CREDIT_NOTE, self::DOCUMENT_TYPE_CAMEL_DEBIT_NOTE])) {
                        throw new InvalidDocumentType([
                            self::DOCUMENT_TYPE_DEBIT_NOTES, self::DOCUMENT_TYPE_CREDIT_NOTES
                        ]);
                    }
                    $statusTranslated = self::DOCUMENT_STATUS_CHANGE_UNSETTLED;
                }
                break;
        }
        return $statusTranslated;
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
        if (!$this->isCreated()) {
            return \InvoiceXpress\Api\Invoice::create($this->getAuth(), $this);
        }
        return \InvoiceXpress\Api\Invoice::update($this->getAuth(), $this);
    }
}