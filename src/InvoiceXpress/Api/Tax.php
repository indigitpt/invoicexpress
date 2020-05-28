<?php


namespace InvoiceXpress\Api;


use InvoiceXpress\Auth;
use InvoiceXpress\Client\Client;
use InvoiceXpress\Entities\Tax as Entity;
use InvoiceXpress\Entities\TaxCollection as EntityCollection;
use InvoiceXpress\Exceptions\InvalidResponse;
use InvoiceXpress\Traits\ApiResource;

class Tax
{
    use ApiResource;

    public const ENTITY = Entity::class;

    /**
     * @param Auth $auth
     * @return EntityCollection
     * @throws InvalidResponse
     */
    public static function list(Auth $auth)
    {
        $request = new Client($auth);
        $response = $request->get(self::getEntity()::ITEMS_URL);
        if ($response->isOk()) {
            $data = $response->get(self::getEntity()::CONTAINER);
            return new EntityCollection($data);
        }
        throw new InvalidResponse($response, $request);
    }
}