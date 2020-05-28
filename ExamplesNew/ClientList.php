<?php

use InvoiceXpress\Api\Clients;
use InvoiceXpress\Exceptions\InvalidResponse;

require('Variables.php');

try {
    # Load the account
    $list = Clients::list($auth, 1, 200);
    # Just so we know
    echo sprintf(
        'Current Page: %s | Per Page: %s | Total Pages: %s | Total entries : %s | Next Page: %s | Previous Page: %s | Has next Page: %s',
        $list->getCurrentPage(),
        $list->getPerPage(),
        $list->getTotalPages(),
        $list->getTotalEntries(),
        $list->getNextPage(),
        $list->getPreviousPage(),
        (int)$list->hasNextPage()
    );
    # Dump the items, the object should be documented with PHPdoc, because the collection is abstract
    //dd($list->getItems());
} catch (Exception $e) {
    if ($e instanceof InvalidResponse) {
        dd($e->getBody(), $e->getBodyAsJson());
    } else {
        dd($e->getMessage());
    }
}
