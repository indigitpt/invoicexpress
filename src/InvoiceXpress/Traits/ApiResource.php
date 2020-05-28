<?php


namespace InvoiceXpress\Traits;

use Illuminate\Support\Arr;
use InvoiceXpress\Auth;
use InvoiceXpress\Client\Client;
use InvoiceXpress\Entities\AbstractEntity;
use InvoiceXpress\Exceptions\InvalidResponse;

trait ApiResource
{
    /**
     * @param Auth $auth
     * @param AbstractEntity $entity
     * @return mixed
     * @throws InvalidResponse
     */
    public static function update(Auth $auth, AbstractEntity $entity)
    {
        $class = self::getEntity();
        $request = new Client($auth);
        $request->addUrlVariable($class::ITEM_IDENTIFIER, $entity->id);
        $data = array_filter($entity->toArrayOnly($class::UPDATE_KEYS));
        $request->addPostFromArray([$class::CONTAINER => $data]);
        $response = $request->put($class::ITEM_URL);
        if ($response->isOk()) {
            $data = $response->get($class::CONTAINER);
            $data['id'] = $entity->id;
            $data = array_merge($data, $entity->toArray());
            return (new $class($data))->withAuth($auth);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param AbstractEntity $entity
     * @return mixed
     * @throws InvalidResponse
     */
    public static function create(Auth $auth, AbstractEntity $entity)
    {
        $class = self::getEntity();
        $request = new Client($auth);
        $data = array_filter($entity->toArrayOnly($class::CREATE_KEYS));
        $request->addPostFromArray([$class::CONTAINER => $data]);
        # We can re-use this exact same code for creating for an existing account. Only the endpoint changes
        $response = $request->post($class::ITEMS_URL);
        if ($response->isOk()) {
            $data = $response->get($class::CONTAINER);
            $entityId = Arr::get($data, 'id');
            # Get the account information right after.
            return self::get($auth, $entityId);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $id
     * @return mixed
     * @throws InvalidResponse
     */
    public static function get(Auth $auth, $id)
    {
        $class = self::getEntity();
        $request = new Client($auth);
        $request->addUrlVariable($class::ITEM_IDENTIFIER, $id);
        $response = $request->get($class::ITEM_URL);
        if ($response->isOk()) {
            $data = $response->get($class::CONTAINER);
            $data['id'] = $id; # Append the id here, since they dont return the ID of the object. SAD times for rest lol
            return (new $class($data))->withAuth($auth);
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @param Auth $auth
     * @param $id
     * @return bool
     * @throws InvalidResponse
     */
    public static function delete(Auth $auth, $id)
    {
        $class = self::getEntity();
        $request = new Client($auth);
        $request->addUrlVariable($class::ITEM_IDENTIFIER, $id);
        $response = $request->delete($class::ITEM_URL);
        if ($response->isOk()) {
            return true;
        }
        throw new InvalidResponse($response, $request);
    }

    /**
     * @return mixed
     */
    public static function getEntity()
    {
        return self::ENTITY;
    }
}