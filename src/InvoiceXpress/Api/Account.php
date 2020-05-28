<?php


namespace InvoiceXpress\Api;

use Illuminate\Support\Arr;
use InvoiceXpress\Auth;
use InvoiceXpress\Client\Client;
use InvoiceXpress\Entities\Account as Entity;
use InvoiceXpress\Exceptions\InvalidResponse;
use InvoiceXpress\Traits\ApiResource;

class Account
{
    use ApiResource;

    public const ENTITY = Entity::class;

    /**
     * @param Auth $auth
     * @param Entity $account
     * @param bool $existing
     * @return Entity
     * @throws InvalidResponse
     */
    public static function create(Auth $auth, Entity $account, $existing = false)
    {
        $request = new Client($auth);
        $data = array_filter($account->toArrayOnly($account::CREATE_KEYS));
        $request->addPostFromArray([$account::CONTAINER => $data]);

        # We can re-use this exact same code for creating for an existing account. Only the endpoint changes
        if ($existing) {
            $request->addUrlVariable('action', 'create_already_user');
            $response = $request->post($account::ITEMS_URL);
        } else {
            $request->addUrlVariable('action', 'create');
            $response = $request->post($account::ITEMS_URL);
        }

        if ($response->isOk()) {

            $data = $response->get($account::CONTAINER);
            $entityId = Arr::get($data, 'id');
            # Get the account information right after.
            return self::get($auth, $entityId);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $id
     * @return Entity
     * @throws InvalidResponse
     */
    public static function get(Auth $auth, $id)
    {
        $request = new Client($auth);
        $request->addUrlVariable(self::getEntity()::ITEM_IDENTIFIER, $id);
        $request->addUrlVariable('action', 'get');
        $response = $request->get(self::getEntity()::ITEM_URL);
        if ($response->isOk()) {
            $data = $response->get(self::getEntity()::CONTAINER);
            $data['id'] = $id; # Append the id here, since they dont return the ID of the object. SAD times for rest lol
            return new Entity($data);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param Entity $account
     * @return Entity
     * @throws InvalidResponse
     */
    public static function update(Auth $auth, Entity $account)
    {
        $request = new Client($auth);
        $request->addUrlVariable($account::ITEM_IDENTIFIER, $account->id);
        $request->addUrlVariable('action', 'update');
        $data = array_filter($account->toArrayOnly($account::UPDATE_KEYS));
        $request->addPostFromArray([$account::CONTAINER => $data]);
        $response = $request->put($account::ITEM_URL);
        if ($response->isOk()) {
            $data = $response->get($account::CONTAINER);
            $data['id'] = $account->getId(); # Append the id here, since they dont return the ID of the object. SAD times for rest lol
            $data = array_merge($data, $account->toArray());
            return new Entity($data);
        }
        throw new InvalidResponse($response, $request);
    }
}