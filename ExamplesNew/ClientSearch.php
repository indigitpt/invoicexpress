<?php

use InvoiceXpress\Api\Clients;
use InvoiceXpress\Constants;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # Load the account
    //$client = Clients::findBy($auth,'XPTO12356',Constants::SEARCH_BY_CODE);
    $client = Clients::findBy($auth, 'Client Name', Constants::SEARCH_BY_NAME);
    dd($client);
} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } else {
        dd($e->getMessage());
    }
}
