<?php


namespace InvoiceXpress;


use InvoiceXpress\Exceptions\InvalidCredentials;

class Auth
{

    /**
     * Stores the API Key
     *
     * @var string $api_key
     */
    protected $api_key;

    /**
     * Stores the Account username
     *
     * @var string $api_key
     */
    protected $account_name;

    /**
     * InvoiceXpress constructor.
     *
     * @param null $account_name
     * @param null $api_key
     * @throws InvalidCredentials
     */
    public function __construct($account_name = null, $api_key = null)
    {
        if (null === $account_name || null === $api_key) {
            throw new InvalidCredentials();
        }

        $this->api_key = $api_key;
        $this->account_name = $account_name;
    }

    /**
     * @return null
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * @param null $api_key
     * @return Auth
     */
    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;
        return $this;
    }

    /**
     * @return null
     */
    public function getAccountName()
    {
        return $this->account_name;
    }

    /**
     * @param null $account_name
     * @return Auth
     */
    public function setAccountName($account_name)
    {
        $this->account_name = $account_name;
        return $this;
    }


}