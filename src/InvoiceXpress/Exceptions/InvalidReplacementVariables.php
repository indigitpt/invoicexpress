<?php


namespace InvoiceXpress\Exceptions;


use Exception;
use Illuminate\Support\Arr;

class InvalidReplacementVariables extends Generic
{
    /**
     * InvalidReplacementVariables constructor.
     *
     * @param array $given
     * @param array $matches
     * @param string $url
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($given = [], $matches = [], $url = '', $code = 0, Exception $previous = null)
    {
        $count_matches = count(Arr::get($matches, 1, []));
        $count_given = count($given);
        $implode_matches = implode(',', Arr::get($matches, 1, []));
        $implode_given = implode(',', $given);
        $message = sprintf(
            'We were expecting %s ( %s ) variables to replace in URL but got %s on URL : %s',
            $count_matches,
            $implode_matches,
            $count_given,
            $url
        );

        $this->addContext('required_keys', Arr::get($matches, 1, []));
        $this->addContext('given_keys', array_keys($given));
        $this->addContext('given_values', $implode_given);
        $this->addContext('url', $url);

        parent::__construct($message, $code, $previous);
    }
}