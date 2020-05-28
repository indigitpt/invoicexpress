<?php


namespace InvoiceXpress\Entities;


use InvoiceXpress\Auth;
use InvoiceXpress\Exceptions\InvalidAuth;
use InvoiceXpress\Exceptions\InvalidResponse;

/**
 * Class Account
 *
 * Lets you create, process and manage payments.
 *
 * @package InvoiceXpress\Entities
 *
 * @property string id
 * @property string organization_name
 * @property string first_name
 * @property string last_name
 * @property string phone
 * @property string email
 * @property integer fiscal_id
 * @property integer tax_country - Options: 1 (Portugal); 2 (Ireland); 3 (UK).
 * @property string language
 * @property string state
 * @property boolean at_configured
 * @property boolean trial
 * @property boolean marketing
 * @property mixed password
 * @property boolean terms
 *
 * @property integer owner_id - not sure if this is the right param for create
 */
class Account extends AbstractEntity
{
    public const CONTAINER = 'account';

    public const ITEM_IDENTIFIER = 'account_id';

    public const ITEM_URL = '/api/accounts/{account_id}/{action}.json';

    public const ITEMS_URL = '/api/accounts/{action}.json';

    public const UPDATE_KEYS = [
        'first_name',
        'last_name',
        'organization_name',
        'phone',
        'email',
        'password',
        'fiscal_id',
        'tax_country',
        'language',
        'terms',
        'marketing'
    ];

    public const CREATE_KEYS = [
        'first_name',
        'last_name',
        'organization_name',
        'phone',
        'email',
        'password',
        'fiscal_id',
        'tax_country',
        'language',
        'terms',
        'marketing'
    ];

    public $_casts = [
        'terms' => 'string',
        'marketing' => 'string',
    ];

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Account
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrganizationName()
    {
        return $this->organization_name;
    }

    /**
     * @param string $organization_name
     * @return Account
     */
    public function setOrganizationName($organization_name)
    {
        $this->organization_name = $organization_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param string $first_name
     * @return Account
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     * @return Account
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return Account
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Account
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return int
     */
    public function getFiscalId()
    {
        return $this->fiscal_id;
    }

    /**
     * @param int $fiscal_id
     * @return Account
     */
    public function setFiscalId($fiscal_id)
    {
        $this->fiscal_id = $fiscal_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getTaxCountry()
    {
        return $this->tax_country;
    }

    /**
     * @param int $tax_country
     * @return Account
     */
    public function setTaxCountry($tax_country)
    {
        $this->tax_country = $tax_country;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return Account
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return Account
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAtConfigured(): bool
    {
        return $this->at_configured;
    }

    /**
     * @param bool $at_configured
     * @return Account
     */
    public function setAtConfigured(bool $at_configured)
    {
        $this->at_configured = $at_configured;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTrial(): bool
    {
        return $this->trial;
    }

    /**
     * @param bool $trial
     * @return Account
     */
    public function setTrial(bool $trial)
    {
        $this->trial = $trial;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMarketing(): bool
    {
        return $this->marketing;
    }

    /**
     * @param bool $marketing
     * @return Account
     */
    public function setMarketing(bool $marketing)
    {
        $this->marketing = $marketing;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return Account
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTerms(): bool
    {
        return $this->terms;
    }

    /**
     * @param bool $terms
     * @return Account
     */
    public function setTerms(bool $terms)
    {
        $this->terms = $terms;
        return $this;
    }

    /**
     * @param int $owner
     * @return Account
     */
    public function setOwner(int $owner)
    {
        $this->owner_id = $owner;
        return $this;
    }

    /**
     * @return int
     */
    public function getOwner(): int
    {
        return $this->owner_id;
    }

    /**
     * @param null|Auth $auth
     * @param bool $existing
     * @return Invoice|mixed|null
     * @throws InvalidAuth
     * @throws InvalidResponse
     */
    public function save($auth = null, $existing = true)
    {
        parent::save($auth);
        if (!$this->isCreated()) {
            return \InvoiceXpress\Api\Account::create($this->getAuth(), $this, $existing);
        }
        return \InvoiceXpress\Api\Account::update($this->getAuth(), $this);
    }
}