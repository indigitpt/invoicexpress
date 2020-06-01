<?php

use InvoiceXpress\Api\Account as AccountAPI;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # Load the account
    $account = AccountAPI::get($auth, $account_id);
    # Update stuff
    $account->withAuth($auth);
    $account->setFirstName('John');
    $account->setLastName('Travolta');
    //$account->setFiscalId('123456799');
    $response = $account->save();
    dd($response);
} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } else {
        dd($e->getMessage());
    }
}
