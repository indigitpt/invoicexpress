<?php


namespace InvoiceXpress\Entities;


use InvoiceXpress\Api\Clients;
use InvoiceXpress\Auth;
use InvoiceXpress\Exceptions\InvalidAuth;
use InvoiceXpress\Exceptions\InvalidResponse;

/**
 * Class Account
 *
 * Lets you create, process and manage clients.
 *
 * @package InvoiceXpress\Entities
 *
 * @property string id
 * @property string name - Company name
 * @property string code
 * @property string email
 * @property string language
 * @property string address
 * @property integer city
 * @property integer postal_code - Options: 1 (Portugal); 2 (Ireland); 3 (UK).
 * @property string country - pt,en,es use contants for this
 * @property string fiscal_id
 * @property string website
 * @property string phone
 * @property string fax
 * @property PreferredContact[] preferred_contact - This on documentation should be idented - https://gyazo.com/87212aa765a7a3eb25dfa2170c0ae9d9
 * @property string observations - Any Notes about this client
 * @property integer send_options - Check the constants for send options
 * @property integer payment_days - Days for payment that this costumer has to pay the invocie ( Maturity Date ) - @see \InvoiceXpress\Constants::CLIENT_PAYMENT_DAYS
 * @property string tax_exemption_code - If this costumer is free from tax ( exemption ) we can provide a code here - - @see \InvoiceXpress\Constants::CLIENT_PAYMENT_DAYS
 * @property string open_account_link - Link to Pending Invoices/Money to the account, provided only AFTER the account is created
 */
class Client extends AbstractEntity
{
    public const ITEM_URL = '/clients/{client_id}.json';

    public const ITEMS_URL = '/clients.json';

    public const ITEM_INVOICES = '/clients/{client_id}/invoices.json';

    public const ITEM_SEARCH_BY_CODE = '/clients/find-by-code.json';

    public const ITEM_SEARCH_BY_NAME = '/clients/find-by-name.json';

    public const CONTAINER = 'client';

    public const ITEM_IDENTIFIER = 'client_id';

    public const UPDATE_KEYS = [
        'name',
        'code',
        'email',
        'language',
        'address',
        'city',
        'postal_code',
        'country',
        'fiscal_id',
        'website',
        'phone',
        'fax',
        'preferred_contact',
        'observations',
        'send_options',
        'currency_id'
    ];

    public const CREATE_KEYS = [
        'name',
        'code',
        'email',
        'language',
        'address',
        'city',
        'postal_code',
        'country',
        'fiscal_id',
        'website',
        'phone',
        'fax',
        'preferred_contact',
        'observations',
        'send_options',
    ];

    public const INVOICE_KEYS = [
        'name',
        'code',
        'email',
        'address',
        'city',
        'postal_code',
        'country',
        'fiscal_id',
        'website',
        'phone',
        'fax',
        'observations',
    ];

    public $_casts = [
        'phone' => 'string',
        'fiscal_id' => 'string'
    ];

    # Clients specific, choose whenever how much documents we should send to the costumer
    # The first option sends original document, second sends original + duplicate, last option sends triplicate.
    public const SEND_OPTION_ONE_DOCUMENT = 1;
    public const SEND_OPTION_TWO_DOCUMENTS = 2;
    public const SEND_OPTION_THREE_DOCUMENTS = 3;

    public const SEND_OPTIONS = [
        self::SEND_OPTION_ONE_DOCUMENT => self::SEND_OPTION_ONE_DOCUMENT,
        self::SEND_OPTION_TWO_DOCUMENTS => self::SEND_OPTION_TWO_DOCUMENTS,
        self::SEND_OPTION_THREE_DOCUMENTS => self::SEND_OPTION_THREE_DOCUMENTS,
    ];

    /**
     * @param string $id
     * @return Client
     */
//    public function setId($id)
//    {
//        $this->id = $id;
//        return $this;
//    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return Client
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $code
     * @return Client
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $email
     * @return Client
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @param string $language
     * @return Client
     */
    public function setLanguage($language)
    {
        $this->language = $language;
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
     * @param string $address
     * @return Client
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param int $city
     * @return Client
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return int
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param int $postal_code
     * @return Client
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    /**
     * @return int
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param string $country
     * @return Client
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $fiscal_id
     * @return Client
     */
    public function setFiscalId($fiscal_id)
    {
        $this->fiscal_id = $fiscal_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getFiscalId()
    {
        return $this->fiscal_id;
    }

    /**
     * @param string $website
     * @return Client
     */
    public function setWebsite($website)
    {
        $this->website = $website;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $phone
     * @return Client
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
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
     * @param string $fax
     * @return Client
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
        return $this;
    }

    /**
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @return PreferredContact[]
     */
    public function getPreferredContact()
    {
        return $this->preferred_contact;
    }

    /**
     * @param string $observations
     * @return Client
     */
    public function setObservations($observations)
    {
        $this->observations = $observations;
        return $this;
    }

    /**
     * @return string
     */
    public function getObservations()
    {
        return $this->observations;
    }

    /**
     * @param int $send_options
     * @return Client
     */
    public function setSendOptions($send_options)
    {
        $this->send_options = $send_options;
        return $this;
    }

    /**
     * @return int
     */
    public function getSendOptions()
    {
        return $this->send_options;
    }

    /**
     * @param int $payment_days
     * @return Client
     */
    public function setPaymentDays($payment_days)
    {
        $this->payment_days = $payment_days;
        return $this;
    }

    /**
     * @return int
     */
    public function getPaymentDays()
    {
        return $this->payment_days;
    }

    /**
     * @param string $tax_exemption_code
     * @return Client
     */
    public function setTaxExemptionCode($tax_exemption_code)
    {
        $this->tax_exemption_code = $tax_exemption_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getTaxExemptionCode()
    {
        return $this->tax_exemption_code;
    }

    /**
     * @return string
     */
    public function getOpenAccountLink()
    {
        return $this->open_account_link;
    }

    /**
     * Here we need to set the method to set the preferred contact for client
     *
     * @param PreferredContact|array $object
     */
    public function setPreferredContact($object)
    {
        if (!$object instanceof PreferredContact) {
            $this->preferred_contact = new PreferredContact($object);
        } else {
            $this->preferred_contact = $object;
        }
    }

    /**
     * @param null|Auth $auth
     * @return Invoice|mixed|null
     * @throws InvalidAuth
     * @throws InvalidResponse
     */
    public function save($auth = null)
    {
        parent::save($auth);

        if (!$this->isCreated()) {
            $object = Clients::create($this->getAuth(), $this);
        }else{
            $object = Clients::update($this->getAuth(), $this);
        }
        $this->fromArray($object->toArray());
        return $this;
    }
}