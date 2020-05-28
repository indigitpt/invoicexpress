<?php


namespace InvoiceXpress\Entities;

use Illuminate\Support\Arr;

/**
 * Class AbstractEntityCollection
 *
 * Used for object collections
 *
 * @package InvoiceXpress\Entities
 *
 * @property array items[]
 * @property integer total_entries - Number of entries that we have on the collection AKA number of users, invoices etc.
 * @property integer current_page - Defines the current page that we are currently on
 * @property integer total_pages - Number of total pages that we have on this collection
 * @property integer per_page - Number of items that we are getting per page.
 */
class AbstractEntityCollection extends AbstractEntity
{
    public $items = [];

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * @return int
     */
    public function getTotalEntries()
    {
        return $this->total_entries;
    }

    /**
     * @return int
     */
    public function getTotalPages()
    {
        return $this->total_pages;
    }

    /**
     * @return int
     */
    public function getPerPage()
    {
        return $this->per_page;
    }

    /**
     * @return int
     */
    public function getNextPage()
    {
        return $this->hasNextPage() ? $this->current_page + 1 : $this->current_page;
    }

    /**
     * @return int
     */
    public function getPreviousPage()
    {
        $count = $this->current_page - 1;
        return $count <= 1 ? 1 : $count;
    }

    /**
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->getCurrentPage() < $this->getTotalPages();
    }

    /**
     * @param array $pagination
     */
    protected function setPagination($pagination = [])
    {
        $this->total_entries = Arr::get($pagination, 'total_entries');
        $this->current_page = Arr::get($pagination, 'current_page');
        $this->total_pages = Arr::get($pagination, 'total_pages');
        $this->per_page = Arr::get($pagination, 'per_page');
    }
}