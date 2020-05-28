<?php


namespace InvoiceXpress\Api;

use Illuminate\Support\Arr;
use InvoiceXpress\Auth;
use InvoiceXpress\Client\Client;
use InvoiceXpress\Entities\DocumentsCollection;
use InvoiceXpress\Entities\Email;
use InvoiceXpress\Entities\Invoice as Entity;
use InvoiceXpress\Entities\Receipt;
use InvoiceXpress\Exceptions\InvalidDocumentType;
use InvoiceXpress\Exceptions\InvalidResponse;
use InvoiceXpress\Exceptions\WaitingPDF;
use InvoiceXpress\Traits\ApiResource;
use InvoiceXpress\Traits\InvoicesAlias;

class Invoice
{
    use ApiResource, InvoicesAlias;

    public const ENTITY = Entity::class;

    /**
     * @param Auth $auth
     * @param Entity $invoice
     * @return Entity
     * @throws InvalidResponse
     */
    public static function create(Auth $auth, Entity $invoice)
    {
        $request = new Client($auth);
        $data = array_filter($invoice->toArrayOnly(self::getEntity()::CREATE_KEYS));
        $request->addPostFromArray([self::getEntity()::CONTAINER => $data]);
        $request->addUrlVariable(self::getEntity()::ITEM_TYPE_IDENTIFIER, $invoice->getTypeInternal());
        $response = $request->post(self::getEntity()::ITEM_CREATE);

        if ($response->isOk()) {
            $document_type_container = $invoice::typeFromPluralToSingular($invoice->getTypeInternal());
            $data = $response->get($document_type_container);
            $entityId = Arr::get($data, 'id');
            return self::get($auth, $entityId, $invoice->getTypeInternal());
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $id
     * @param $document_type
     * @return Entity
     * @throws InvalidResponse
     */
    public static function get(Auth $auth, $id, $document_type)
    {
        $request = new Client($auth);
        $request->addUrlVariable(self::getEntity()::ITEM_IDENTIFIER, $id);
        $request->addUrlVariable(self::getEntity()::ITEM_TYPE_IDENTIFIER, $document_type);
        $response = $request->get(self::getEntity()::ITEM_URL);
        if ($response->isOk()) {
            $document_type_container = self::getEntity()::typeFromPluralToSingular($document_type);
            $data = $response->get($document_type_container);
            $data['id'] = $id; # Append the id here, since they dont return the ID of the object. SAD times for rest lol
            return (new Entity($data))->withAuth($auth);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param Entity $invoice
     * @return Entity
     * @throws InvalidResponse
     */
    public static function update(Auth $auth, Entity $invoice)
    {
        $request = new Client($auth);
        $data = array_filter($invoice->toArrayOnly($invoice::CREATE_KEYS));
        $request->addPostFromArray([$invoice::CONTAINER => $data]);
        $request->addUrlVariable($invoice::ITEM_TYPE_IDENTIFIER, $invoice->getTypeToPlural());
        $request->addUrlVariable($invoice::ITEM_IDENTIFIER, $invoice->getId());

        $response = $request->put($invoice::ITEM_URL);
        if ($response->isOk()) {
            return self::get($auth, $invoice->getId(), $invoice->getTypeToPlural());
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param Entity $invoice
     * @param string $to_state - This status is not actually the regular states, please check the method stateUpdateChecker() or use it to convert and check the appropriate states
     * @param string $reason
     * @return Entity
     * @throws InvalidResponse
     */
    public static function state(
        Auth $auth,
        Entity $invoice,
        $to_state = Entity::DOCUMENT_STATUS_CHANGE_FINAL,
        $reason = 'Observations'
    )
    {

        $request = new Client($auth);
        $request->addUrlVariable($invoice::ITEM_IDENTIFIER, $invoice->getId());
        $request->addUrlVariable($invoice::ITEM_TYPE_IDENTIFIER, $invoice->getTypeToPlural());
        $request->addUrlVariable('action', 'change-state');
        $request->addPostFromArray([$invoice::CONTAINER => ['state' => $to_state, 'message' => $reason]]);
        $response = $request->put($invoice::ITEM_ACTION);
        if ($response->isOk()) {
            # The documentation once again leads to wrong interpretations, we should get the container as the same type of object.
            # Also to create object we use plural method of the name, to filters we use Camel Case and here we use singular, what a mess..
            return self::get($auth, $invoice->getId(), $invoice->getTypeToPlural());
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param Entity $invoice
     * @param Receipt $receipt
     * @return Receipt
     * @throws InvalidDocumentType
     * @throws InvalidResponse
     */
    public static function receiptCreate(Auth $auth, Entity $invoice, Receipt $receipt)
    {

        if (!$invoice->canCreateReceipt()) {
            throw new InvalidDocumentType([
                Entity::DOCUMENT_TYPE_INVOICE, Entity::DOCUMENT_TYPE_INVOICE_SIMPLIFIED,
                Entity::DOCUMENT_TYPE_VAT_MOSS_INVOICE
            ]);
        }

        $request = new Client($auth);
        $data = array_filter($receipt->toArrayOnly($receipt::CREATE_KEYS));
        $request->addPostFromArray([$receipt::CONTAINER_CREATE => $data]);
        $request->addUrlVariable($invoice::ITEM_IDENTIFIER, $invoice->getId());
        $response = $request->post($receipt::ITEM_CREATE);

        if ($response->isOk()) {
            $data = $response->get($receipt::CONTAINER);
            return new Receipt($data);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $receipt_id
     * @param $message
     * @return Receipt
     * @throws InvalidResponse
     */
    public static function receiptCancel(Auth $auth, $receipt_id, $message)
    {
        $request = new Client($auth);
        $request->addPostFromArray([
            Receipt::CONTAINER => Arr::only([
                'state' => Entity::DOCUMENT_STATUS_CANCELED, 'message' => $message
            ], Receipt::CANCEL_KEYS)
        ]);
        $request->addUrlVariable(Receipt::ITEM_IDENTIFIER, $receipt_id);
        $request->addUrlVariable('action', 'change-state');
        $response = $request->put(Receipt::ITEM_URL);
        if ($response->isOk()) {
            $data = $response->get(Receipt::CONTAINER);
            return (new Receipt($data))->withAuth($auth);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $filters
     * @return Receipt
     * @throws InvalidResponse
     */
    public static function list(Auth $auth, $filters)
    {
        $request = new Client($auth);
        $response = $request->put(Receipt::ITEM_URL);
        if ($response->isOk()) {
            $data = $response->get(Receipt::CONTAINER);
            return (new Receipt($data))->withAuth($auth);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $id
     * @param bool $second_copy
     * @return mixed
     * @throws InvalidResponse
     * @throws WaitingPDF
     */
    public static function pdf(Auth $auth, $id, $second_copy = false)
    {
        $request = new Client($auth);
        $request->addUrlVariable(self::getEntity()::ITEM_IDENTIFIER, $id);
        $request->addQuery('second_copy', $second_copy);
        $response = $request->get(self::getEntity()::ITEM_PDF);
        if ($response->isOk()) {
            # Its still doing stuff
            if ($response->getStatusCode() === 202) {
                throw new WaitingPDF();
            }
            # Its done
            if ($response->getStatusCode() === 200) {
                return $response->get('output.pdfUrl', null);
            }
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $id
     * @param bool $second_copy
     * @return mixed
     * @throws InvalidResponse
     * @throws WaitingPDF
     */
    public static function pdfAlternative(Auth $auth, $id, $second_copy = false)
    {
        $request = new Client($auth);
        $request->addUrlVariable(self::getEntity()::ITEM_IDENTIFIER, $id);
        $request->addUrlVariable('second_copy', $second_copy ? 'true' : 'false');
        $response = $request->get(self::getEntity()::ITEM_PDF_DOWNLOAD);
        if ($response->isOk()) {
            # Its still doing stuff
            if ($response->getStatusCode() === 202) {
                throw new WaitingPDF();
            }
            # Its done
            if ($response->getStatusCode() === 200) {
                return $response->get('pdfUrl', null);
            }
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $id
     * @return DocumentsCollection
     * @throws InvalidResponse
     */
    public static function relatedDocuments(Auth $auth, $id)
    {
        $request = new Client($auth);
        $request->addUrlVariable(self::getEntity()::ITEM_IDENTIFIER, $id);
        $request->addUrlVariable('action', 'related_documents');
        $response = $request->get(self::getEntity()::ITEM_DOCUMENTS);
        if ($response->isOk()) {
            return new DocumentsCollection($response->getBody());
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param Entity $invoice
     * @param Email $email
     * @return bool
     */
    public static function emailSend(Auth $auth, Entity $invoice, Email $email)
    {
        $request = new Client($auth);
        $request->addUrlVariable(Entity::ITEM_IDENTIFIER, $invoice->id);
        $request->addUrlVariable(Entity::ITEM_TYPE_IDENTIFIER, $invoice->getTypeToPlural());
        $request->addUrlVariable('action', 'email-document');
        $request->addPostFromArray([Email::CONTAINER => $email->toArray()]);
        $response = $request->put(Entity::ITEM_ACTION);
        if ($response->isOk()) {
            return true;
        }
        return false;
    }
}