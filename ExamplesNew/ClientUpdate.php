<?php

use InvoiceXpress\Api\Clients;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # Load the account
    $client = Clients::get($auth, $client_id);

    $client->withAuth($auth);
    $client->setEmail($faker->email);
    $response = $client->save();
    dd($response);
} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } else {
        dd($e->getMessage());
    }
}
