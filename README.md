# Laravel Localization

[![Latest Stable Version](https://poser.pugx.org/indigit/invoicexpress/v)](//packagist.org/packages/indigit/invoicexpress) [![Total Downloads](https://poser.pugx.org/indigit/invoicexpress/downloads)](//packagist.org/packages/indigit/invoicexpress) [![Latest Unstable Version](https://poser.pugx.org/indigit/invoicexpress/v/unstable)](//packagist.org/packages/indigit/invoicexpress) [![License](https://poser.pugx.org/indigit/invoicexpress/license)](//packagist.org/packages/indigit/invoicexpress)

This package offers a simple way to create invoices using InvoiceXpress API. InvoiceXpress is a billing/invoicing plataforms available in Portugal and some other countries.
After some time looking for a package we didn+t found any that fit our needs so here we are!
Please keep in mind some of the endpoints are still to be implement please feel free to push a PR. We don't have any plans right now on finishing them since we don't need it for our own use case.
This plugin comes with some Laravel dependencies and tools but it should work out of the box on any non-laravel project

## Endpoints Working

- [x] Invoices: Send By Email
- [x] Invoices: Send PDF
- [x] Invoices: Get / Create / Update / Change State
- [x] Invoices: List All
- [x] Invoices: Related Documents
- [x] Invoices: Create / Cancel Payment Receipts
- [ ] Estimates: Not implemented yet
- [ ] Guides: Not implemented yet
- [ ] Purchase Orders: Not implemented yet
- [x] Clients: List All
- [x] Clients: Get / Create / Update / Invoices
- [x] Clients: Find By Code / Find by Name
- [x] Items: Get / Create / Update / Delete / List
- [ ] Sequences: Not implemented yet
- [x] Taxes: Get / Create / Update / Delete / List
- [x] Accounts: Get / Create / Update / Create Existing

## Requirements

```
"php": ">=7.2.0",
"ext-curl": "*",
"ext-json": "*",
"fortis/iso-currency": "^1.0",
"guzzlehttp/guzzle": "^7.0",
"illuminate/support": "~5.8.0|^6.0|^7.0",
"laravel/helpers": "^1.1",
"nesbot/carbon": "^2.31"
```

## Installation

Install the package via composer: `composer require indigit/invoicexpress`


### Example of Auth Usage

Each request requires Authentication via Query Parameter, we have done it in a easy way.
You should create an InvoiceXpress\Auth by passing your API Key and Account username that can be found at : Account -> Integrations -> API

```php
<?php
use InvoiceXpress\Auth;
use InvoiceXpress\Api\Invoice;
use InvoiceXpress\Exceptions\Generic;
use InvoiceXpress\Exceptions\InvalidResponse;

$auth = new Auth('YOUR_ACCOUNT_NAME', 'YOUR_API_KEY');

try {
    # Create the base invoice Item
    $invoice = Invoice::get($auth, 123456, \InvoiceXpress\Entities\Invoice::DOCUMENT_TYPE_INVOICE);
} catch (\Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } elseif ($e instanceof Generic) {
        dd($e->getMessage(), $e->getContext());
    } else {
        dd($e->getMessage());
    }
    dd($e);
}
```

## Usage Examples

For more examples and see how it works please check the "Examples" Folder.

### Recommendations

***1.***: It is **strongly** recommended to use a .env file to pass your credentials and API keys into the auth object. Never save it with clear text within the project.

***2.***: It is **strongly** recommended to create some sort of database logic to keep track of invoices_types and PKs, since InvoiceXpress API has a lot of document types. This could have been easly doen with only one implementation of "Documents" into a single endpoint


## Collaborators
- [Pedro Martins](https://github.com/indigitpt)
- [Nuno Xavier](https://github.com/mrnsane)

## License

InvoiceXpress is an open-sourced PHP package licensed under the MIT license and cannot be sold or licensed by any means.
