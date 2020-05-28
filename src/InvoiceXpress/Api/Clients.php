<?php


namespace InvoiceXpress\Api;


use InvoiceXpress\Auth;
use InvoiceXpress\Client\Client;
use InvoiceXpress\Constants;
use InvoiceXpress\Entities\Client as Entity;
use InvoiceXpress\Entities\ClientsCollection as EntityCollection;
use InvoiceXpress\Exceptions\InvalidResponse;
use InvoiceXpress\Exceptions\InvalidSearchType;
use InvoiceXpress\Traits\ApiResource;

class Clients
{
    use ApiResource;

    public const ENTITY = Entity::class;

    /**
     * @param Auth $auth
     * @param int $page
     * @param int $per_page
     * @return EntityCollection
     * @throws InvalidResponse
     */
    public static function list(Auth $auth, $page = 1, $per_page = 30)
    {
        $request = new Client($auth);
        # We can re-use this exact same code for creating for an existing account. Only the endpoint changes
        $request->addQuery('page', $page);
        $request->addQuery('per_page', $per_page);
        $response = $request->get(self::getEntity()::ITEMS_URL);
        if ($response->isOk()) {
            return new EntityCollection($response->getBody());
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $query
     * @param string $type
     * @return Entity
     * @throws InvalidResponse
     * @throws InvalidSearchType
     */
    public static function findBy(Auth $auth, $query, $type = Constants::SEARCH_BY_CODE)
    {
        switch ($type) {
            case Constants::SEARCH_BY_CODE;
                $search_string = 'client_code';
                $endpoint = self::getEntity()::ITEM_SEARCH_BY_CODE;
                break;
            case Constants::SEARCH_BY_NAME;
                $search_string = 'client_name';
                $endpoint = self::getEntity()::ITEM_SEARCH_BY_NAME;
                break;
            default:
                throw new InvalidSearchType();
                break;
        }
        $request = new Client($auth);
        # We can re-use this exact same code for creating for an existing account. Only the endpoint changes
        $request->addQuery($search_string, $query);
        $response = $request->get($endpoint);
        if ($response->isOk()) {
            $data = $response->get(Entity::CONTAINER);
            return (new Entity($data))->withAuth($auth);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param int $page
     * @param int $per_page
     * @param array $filters
     * @return EntityCollection
     * @throws InvalidResponse
     */
    public static function invoices(Auth $auth, $page = 1, $per_page = 30, $filters = [])
    {
        $request = new Client($auth);
        # We can re-use this exact same code for creating for an existing account. Only the endpoint changes
        $request->addQuery('page', $page);
        $request->addQuery('per_page', $per_page);
        $response = $request->get(self::getEntity()::ITEM_INVOICES);
        if ($response->isOk()) {
            return new EntityCollection($response->getBody());
        }
        throw new InvalidResponse($response, $request);
    }
}