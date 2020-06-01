<?php

use InvoiceXpress\Constants;
use InvoiceXpress\Entities\Client;
use InvoiceXpress\Entities\PreferredContact;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    $client = Client::make($auth);
    $client->setName($faker->name);
    $client->setCode('XPTO12356'); // Change this to unique ID that identify your client, i recommend using here a addon called Hashids
    $client->setEmail($faker->email);
    $client->setLanguage(Constants::LANGUAGE_PT);
    $client->setAddress($faker->address);
    $client->setCity($faker->city);
    $client->setPostalCode($faker->postcode);
    $client->setCountry(Constants::COUNTRY_UNITED_ARAB_EMIRATES); // We use UAE to avoid VAT number validations.
    //$client->setCountry(Constants::COUNTRY_PORTUGAL); // if you use portugal make sure faker generates valid vat_ids
    $client->setFiscalId($faker->taxpayerIdentificationNumber);
    $client->setObservations($faker->realText(10));
    $client->setSendOptions(Client::SEND_OPTION_THREE_DOCUMENTS);

    # Preferred Contact Create here :)
    $preferredContact = new PreferredContact();
    $preferredContact->setName($faker->name);
    $preferredContact->setEmail($faker->email);
    $preferredContact->setPhone($faker->e164PhoneNumber);

    # Bind
    $client->setPreferredContact($preferredContact);

    $response = $client->save();
    dd($response->getId());
} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } else {
        dd($e->getMessage());
    }
}
