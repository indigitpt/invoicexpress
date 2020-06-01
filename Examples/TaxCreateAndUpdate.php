<?php

use InvoiceXpress\Entities\Tax;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # $tax = \InvoiceXpress\Api\Tax::get($auth,$tax_id); // For updates use this parameter
    $tax = Tax::make($auth);
    $tax->setName('TaxaDe23');
    $tax->setValue(23);
    $tax->setRegion(Tax::TAX_REGION_PORTUGAL);
    $tax->setDefaultTax(false);
    $response = $tax->save();
    dd($response);
} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } else {
        dd($e->getMessage());
    }
}
