<?php

namespace InvoiceXpress\Traits;

use InvoiceXpress\Auth;
use InvoiceXpress\Entities\Invoice;
use InvoiceXpress\Exceptions\InvalidDocumentType;
use InvoiceXpress\Exceptions\InvalidResponse;

trait InvoicesAlias
{

    /**
     * Simple alias to create invoice
     *
     * @param Invoice $invoice
     * @param Auth $auth
     * @return Invoice
     * @throws InvalidDocumentType
     * @throws InvalidResponse
     */
    public static function createInvoice(Invoice $invoice, Auth $auth)
    {
        return self::create($auth, $invoice);
    }

    /**
     * Simple alias for Create Invoice Receipt
     *
     * @param Invoice $invoice
     * @param Auth $auth
     * @return Invoice
     */
    public static function createInvoiceReceipt(Invoice $invoice, Auth $auth)
    {
        return self::create($auth, $invoice);
    }

    /**
     * @param $id
     * @param Auth $auth
     * @return Invoice
     */
    public static function getInvoice($id, Auth $auth)
    {
        return self::get($auth, $id, Invoice::DOCUMENT_TYPE_INVOICE);
    }

    /**
     * @param $id
     * @param Auth $auth
     * @return Invoice
     */
    public static function getInvoiceReceipt($id, Auth $auth)
    {
        return self::get($auth, $id, Invoice::DOCUMENT_TYPE_INVOICE);
    }
}