<?php

use Carbon\Carbon;
use Currency\CurrencyCode;
use InvoiceXpress\Api\Clients;
use InvoiceXpress\Constants;
use InvoiceXpress\Entities\Invoice;
use InvoiceXpress\Entities\InvoiceItem;
use InvoiceXpress\Entities\Tax;
use InvoiceXpress\Exceptions\Generic;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # Create the base invoice Item
    $client = Clients::findBy($auth, 'John Travolta', Constants::SEARCH_BY_NAME);
    $tax23IVA = (new Tax(['name' => 'IVA23']))->toArrayOnly(Tax::ITEMS_KEYS);

    $item1 = new InvoiceItem();
    $item1
        ->setName('Coca-Cola')
        ->setDescription('Fresh coca-cola from Portugal')
        ->setUnitPrice(10)
        ->setQuantity(10)
        ->setUnit($item1::UNIT_TYPE_UNIT)
        ->setDiscount(0)
        ->setTax($tax23IVA);
    $item2 = clone $item1;
    $item2->setUnitPrice(5);

    $invoice = new Invoice();
    $response = $invoice
        ->withAuth($auth)
        ->typeInvoiceReceipt() # You can define other type of types here: InvoiceReceipt etc.
        ->setDate(Carbon::now())
        ->setDueDate(Carbon::now()->addMonth())
        ->setReference(md5(time()))
        ->setObservations('This is a sample invoice.')
        ->setRetention(10)
        ->setMbReferencesEnable()
        ->setMbReferencesDisable()
        ->setCurrencyCode(CurrencyCode::EUR)
        ->addClient($client)
        ->addItem($item1)
        ->addItem($item2)
        ->exportingToEurope() # This method should be called only after items are added otherwise you must be carefull to provide a exemption reason and remove the vat from items
        ->save()
        //->setSequenceId(null)
        //->setManualSequenceNumber(null)
        //->setTaxExemptionReason(\InvoiceXpress\Entities\Tax::TAX_EXEMPTION_M00)
        //->setTaxExemption(\InvoiceXpress\Entities\Tax::TAX_EXEMPTION_M00)
    ;
    dd($response);

    # Set the document Type
    //$type = $invoice::DOCUMENT_TYPE_INVOICE_RECEIPTS;
    //$response = \InvoiceXpress\Api\Invoice::create($invoice,$type,$auth);
    //dd($response->getItems());

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
