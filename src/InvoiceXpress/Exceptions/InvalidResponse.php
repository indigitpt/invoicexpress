<?php


namespace InvoiceXpress\Exceptions;

use Exception;
use Illuminate\Support\Arr;
use InvoiceXpress\Client\Response;

class InvalidResponse extends Generic
{
    /**
     * InvalidResponse constructor.
     *
     * @param null $response
     * @param null $request
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($response = null, $request = null, $code = 3001, Exception $previous = null)
    {
        $message = '';
        if ($response instanceof Response) {
            $message = $response->getResponse()->getReasonPhrase();
            # Unprocessable Entity
            # This should mean that the resource we pretend to update has some sort of errors
            if ($response->getStatusCode() === 422) {
                $body = $response->getBody();
                if (is_array($body)) {
                    // ['nif','o nif é invalid']
                    // ['nif' => ['ta mal','ta bem']
                    //$this->addContext('errors_string', $this->errors_dump($body));
                    //$this->addContext('errors', $this->errors_dump($body, false));
                    $this->addContext('errors', $body);
                }
            }
            $this->addContext('body', $response->getBody());
            $this->addContext('headers', $response->getHeaders());
            $this->addContext('status_code', $response->getStatusCode());
            $this->addContext('response', $response->getResponse());
            $this->addContext('request', $request);
        }

        if ($response === null) {
            $message = 'The response is empty, null or timed out.';
        }

        $this->addContext('message', $message);
        parent::__construct(sprintf('InvoiceXpress: There an error with the request: %s', $message), $code, $previous);
    }

    /**
     * Assuming the errors always come
     * The errors output is so messy that i cannot yet figure out to proper parse them
     *
     * @param $array
     * @param bool $stringify
     * @return string|array
     */
    private function errors_dump($array, $stringify = true)
    {
        $errors = [];
        $errors_string = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $key = Arr::get($v, 0);
                $value = Arr::get($v, 1);
                if (null !== $key && null !== $value) {
                    $errors[$key][] = $value;
                    $errors_string[] = Arr::get($v, 0) . ' - ' . Arr::get($v, 1);
                }
            } else {
                $errors_string[] = $v;
            }
        }
        if ($stringify) {
            return implode(', ', $errors_string);
        }
        return $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->getContext('errors');
    }

    /**
     * @return string
     */
    public function getErrorsAsString()
    {
        return $this->getContext('errors_string');
    }

    /**
     * @return string
     */
    public function getStatusCode()
    {
        return $this->getContext('status_code');
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->getContext('body');
    }

    /**
     * @return string
     */
    public function getBodyAsJson()
    {
        return json_encode($this->getContext('body') ?? '[]');
    }
}