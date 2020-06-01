<?php

use InvoiceXpress\Api\Account as AccountAPI;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # Load the account
    $account = AccountAPI::get($auth, $account_id);
    dd($account->pre);
} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } else {
        dd($e->getMessage());
    }
}
