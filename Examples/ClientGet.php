<?php

use InvoiceXpress\Api\Clients;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # Load the account
    $client = Clients::get($auth, $client_id);
    dd($client);
} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } else {
        dd($e->getMessage());
    }
}
