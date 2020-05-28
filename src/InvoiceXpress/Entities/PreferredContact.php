<?php


namespace InvoiceXpress\Entities;


/**
 * Class ClientPreferredContact
 *
 * Used to manage client preferred contacts
 *
 * @package InvoiceXpress\Entities
 *
 * @property string id
 * @property string name
 * @property string email
 * @property string phone
 */
class PreferredContact extends AbstractEntity
{
    /**
     * Defines the array entry point with dot notation
     *
     * @var string $_container
     */
    public const CONTAINER = 'preferred_contact';

    public $_casts = [
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
    ];

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return PreferredContact
     */
    public function setName($name): PreferredContact
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
     * @param string $email
     * @return PreferredContact
     */
    public function setEmail($email): PreferredContact
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
     * @param string $phone
     * @return PreferredContact
     */
    public function setPhone($phone): PreferredContact
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
}