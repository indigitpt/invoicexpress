<?php


namespace InvoiceXpress\Entities;

/**
 * Class Taxt
 *
 * Lets you create, process and manage clients.
 *
 * @package InvoiceXpress\Entities
 *
 * @property Client client
 * @property string subject - Subject of the email
 * @property string body - Body of the email
 * @property string cc - Send email also to
 * @property string bcc - Email BCC
 * @property integer logo - Attach the logo the email or not
 *
 */
class Email extends AbstractEntity
{
    public const CONTAINER = 'message';

    public const CLIENT_KEYS = [
        'email',
        'save',
    ];

    protected $_casts = [
        'logo' => 'integer'
    ];

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     * @param bool $save
     * @return Email
     */
    public function setClient(Client $client, $save = false)
    {
        $this->client = $client->toArrayOnly(self::CLIENT_KEYS) + ['save' => (int)$save];
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return Email
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return Email
     */
    public function setBody(string $body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getCc(): string
    {
        return $this->cc;
    }

    /**
     * @param string $cc
     * @return Email
     */
    public function setCc(string $cc)
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * @return string
     */
    public function getBcc(): string
    {
        return $this->bcc;
    }

    /**
     * @param string $bcc
     * @return Email
     */
    public function setBcc(string $bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @return int
     */
    public function getLogo(): int
    {
        return $this->logo;
    }

    /**
     * @param int $logo
     * @return Email
     */
    public function setLogo(int $logo)
    {
        $this->logo = $logo;
        return $this;
    }
}