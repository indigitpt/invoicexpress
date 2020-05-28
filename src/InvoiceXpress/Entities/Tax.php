<?php


namespace InvoiceXpress\Entities;

use InvoiceXpress\Auth;
use InvoiceXpress\Exceptions\InvalidAuth;
use InvoiceXpress\Exceptions\InvalidResponse;

/**
 * Class Taxt
 *
 * Lets you create, process and manage clients.
 *
 * @package InvoiceXpress\Entities
 *
 * @property string id
 * @property string name - Name of the tax : Ex: IVA23
 * @property string value - Value of the tax : Ex: 23 stands for 23% this needs to be a STRING
 * @property string region - Region, @see Constants::TAX_REGIONS
 * @property integer|boolean default_tax - If its the default tax to be used
 *
 *
 */
class Tax extends AbstractEntity
{
    # Enums for Tax Countries used for account update and probably for automatic invoice reporting to gov.
    public const TAX_COUNTRY_PORTUGAL = 1;
    public const TAX_COUNTRY_IRELAND = 2;
    public const TAX_COUNTRY_UK = 3;

    public const TAX_COUNTRIES = [
        self::TAX_COUNTRY_PORTUGAL,
        self::TAX_COUNTRY_IRELAND,
        self::TAX_COUNTRY_UK,
    ];

    # Tax Exemption Reasons
    # @see https://invoicexpress.com/new-api-v2/documentation/appendix for more information
    public const TAX_EXEMPTION_M00 = 'M00'; # Inherit from global configuration / MUST pay Taxes
    public const TAX_EXEMPTION_M01 = 'M01';
    public const TAX_EXEMPTION_M02 = 'M02';
    public const TAX_EXEMPTION_M03 = 'M03';
    public const TAX_EXEMPTION_M04 = 'M04';
    public const TAX_EXEMPTION_M05 = 'M05';
    public const TAX_EXEMPTION_M06 = 'M06';
    public const TAX_EXEMPTION_M07 = 'M07';
    public const TAX_EXEMPTION_M08 = 'M08';
    public const TAX_EXEMPTION_M09 = 'M09';
    public const TAX_EXEMPTION_M10 = 'M10';
    public const TAX_EXEMPTION_M11 = 'M11';
    public const TAX_EXEMPTION_M12 = 'M12';
    public const TAX_EXEMPTION_M13 = 'M13';
    public const TAX_EXEMPTION_M14 = 'M14';
    public const TAX_EXEMPTION_M15 = 'M15';
    public const TAX_EXEMPTION_M16 = 'M16'; # Most common from RITI
    public const TAX_EXEMPTION_M99 = 'M99';

    public const TAX_EXEMPTION_REASONS = [
        self::TAX_EXEMPTION_M00,
        self::TAX_EXEMPTION_M01,
        self::TAX_EXEMPTION_M02,
        self::TAX_EXEMPTION_M03,
        self::TAX_EXEMPTION_M04,
        self::TAX_EXEMPTION_M05,
        self::TAX_EXEMPTION_M06,
        self::TAX_EXEMPTION_M07,
        self::TAX_EXEMPTION_M08,
        self::TAX_EXEMPTION_M09,
        self::TAX_EXEMPTION_M10,
        self::TAX_EXEMPTION_M11,
        self::TAX_EXEMPTION_M12,
        self::TAX_EXEMPTION_M13,
        self::TAX_EXEMPTION_M14,
        self::TAX_EXEMPTION_M15,
        self::TAX_EXEMPTION_M16,
        self::TAX_EXEMPTION_M99,
    ];

    public const TAX_EXEMPTION_REASONS_TEXT = [
        self::TAX_EXEMPTION_M00 => 'Não Aplicavel',
        self::TAX_EXEMPTION_M01 => 'Artigo 16.º n.º 6 alínea c) do CIVA',
        self::TAX_EXEMPTION_M02 => 'Artigo 6.º do Decreto‐Lei n.º 198/90, de 19 de Junho',
        self::TAX_EXEMPTION_M03 => 'Exigibilidade de caixa',
        self::TAX_EXEMPTION_M04 => 'Isento Artigo 13.º do CIVA',
        self::TAX_EXEMPTION_M05 => 'Isento Artigo 14.º do CIVA', # Most common for exporting world
        self::TAX_EXEMPTION_M06 => 'Isento Artigo 15.º do CIVA',
        self::TAX_EXEMPTION_M07 => 'Isento Artigo 9.º do CIVA',
        self::TAX_EXEMPTION_M08 => 'IVA – Autoliquidação',
        self::TAX_EXEMPTION_M09 => 'IVA ‐ não confere direito a dedução',
        self::TAX_EXEMPTION_M10 => 'IVA – Regime de isenção',
        self::TAX_EXEMPTION_M11 => 'Regime particular do tabaco',
        self::TAX_EXEMPTION_M12 => 'Regime da margem de lucro – Agências de Viagens',
        self::TAX_EXEMPTION_M13 => 'Regime da margem de lucro – Bens em segunda mão',
        self::TAX_EXEMPTION_M14 => 'Regime da margem de lucro – Objetos de arte',
        self::TAX_EXEMPTION_M15 => 'Regime da margem de lucro – Objetos de coleção e antiguidades',
        self::TAX_EXEMPTION_M16 => 'Isento Artigo 14.º do RITI ( Exportaçao para Europa - Empresas )',
        # Most common to Europe
        self::TAX_EXEMPTION_M99 => 'Não sujeito; não tributado (ou similar)',
    ];

    # Tax Regions used for Tax Object
    public const TAX_REGION_PORTUGAL = 'PT';
    public const TAX_REGION_PORTUGAL_AZORES = 'PT-AC';
    public const TAX_REGION_PORTUGAL_MADEIRA = 'PT-MA';
    public const TAX_REGION_PORTUGAL_UNKNOWN = 'Unknown';

    public const TAX_REGIONS = [
        self::TAX_REGION_PORTUGAL => self::TAX_REGION_PORTUGAL,
        self::TAX_REGION_PORTUGAL_AZORES => self::TAX_REGION_PORTUGAL_AZORES,
        self::TAX_REGION_PORTUGAL_MADEIRA => self::TAX_REGION_PORTUGAL_MADEIRA,
        self::TAX_REGION_PORTUGAL_UNKNOWN => self::TAX_REGION_PORTUGAL_UNKNOWN,
    ];

    public const ITEM_URL = '/taxes/{tax_id}.json';
    public const ITEMS_URL = '/taxes.json';
    public const CONTAINER = 'tax';
    public const ITEM_IDENTIFIER = 'tax_id';
    public const UPDATE_KEYS = [
        'tax',
        'name',
        'value',
        'region',
        'default_tax',
    ];

    public const CREATE_KEYS = [
        'tax',
        'name',
        'value',
        'region',
        'default_tax',
    ];

    public const ITEMS_KEYS = [
        'name'
    ];

    # Inside the ITEM object, what keys we want to inject
    protected $_casts = [
        'value' => 'string',
        'default_tax' => 'integer'
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
     * @return Tax
     */
    public function setName($name): Tax
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
     * @param string $value
     * @return Tax
     */
    public function setValue($value): Tax
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $region
     * @return Tax
     */
    public function setRegion($region): Tax
    {
        $this->region = $region;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param bool|int $default_tax
     * @return Tax
     */
    public function setDefaultTax($default_tax)
    {
        $this->default_tax = $default_tax;
        return $this;
    }

    /**
     * @return bool|int
     */
    public function getDefaultTax()
    {
        return $this->default_tax;
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
            return \InvoiceXpress\Api\Tax::create($this->getAuth(), $this);
        }
        return \InvoiceXpress\Api\Tax::update($this->getAuth(), $this);
    }

}