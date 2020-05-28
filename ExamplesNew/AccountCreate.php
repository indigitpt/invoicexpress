<?php

use InvoiceXpress\Constants;
use InvoiceXpress\Entities\Account;
use InvoiceXpress\Entities\Tax;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    $account = Account::make($auth);
    $account->setFirstName('John');
    $account->setLastName('Doe');
    $account->setOrganizationName('My Company');
    $account->setPhone('916241231');
    $account->setEmail('foo@bar.pt');
    $account->setPassword('12356546');
    $account->setFiscalId($faker->taxpayerIdentificationNumber);
    $account->setTaxCountry(Tax::TAX_COUNTRY_PORTUGAL);
    $account->setLanguage(Constants::LANGUAGE_PT);
    $account->setTerms(Constants::ACCEPTED);
    $account->setMarketing(Constants::ACCEPTED);

    $response = $account->save(null, false);
    dd($response);
} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } else {
        dd($e->getMessage());
    }
}
