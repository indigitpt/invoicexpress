<?php

use Carbon\Carbon;
use InvoiceXpress\Api\Invoice;
use InvoiceXpress\Constants;
use InvoiceXpress\Entities\Receipt;
use InvoiceXpress\Exceptions\Generic;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # Create the base invoice Item
    $invoice = Invoice::get($auth, $invoice_id, \InvoiceXpress\Entities\Invoice::DOCUMENT_TYPE_INVOICE);
    # Create the receipt
    $receipt = new Receipt();
    $receipt->setAmount(1);
    $receipt->setPaymentMechanism(Constants::PAYMENT_METHOD_OTHER);
    $receipt->setNote('Pagamento em partes');
    $receipt->setInvoiceId($invoice->id);
    $receipt->setPaymentDate(Carbon::now()->addMonth());

    # Create the Receipt
    $receipt = $invoice->createReceipt($receipt);
    dd($receipt);

} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } elseif ($e instanceof Generic) {
        dd($e->getMessage(), $e->getContext());
    } else {
        dd($e->getMessage());
    }
    dd($e);
}
