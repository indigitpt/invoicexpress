<?php

use InvoiceXpress\Api\Clients;
use InvoiceXpress\Api\Invoice;
use InvoiceXpress\Entities\Email;
use InvoiceXpress\Exceptions\Generic;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # Create the base invoice Item
    $invoice = Invoice::get($auth, $invoice_id, \InvoiceXpress\Entities\Invoice::DOCUMENT_TYPE_INVOICE);
    $client = Clients::get($auth, $client_id);

    $email = Email::make($auth);
    $email->setSubject('A sua factura de teste');
    $email->setClient($client);
    $email->setBody('Em anexo segue a sua factura');
    $email->setCc('nikuscs@gmail.com');

    $invoice->withAuth($auth);
    $invoice->sendEmail($email);

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
