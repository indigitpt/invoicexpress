<?php

namespace InvoiceXpress\Client;


use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * @var array|null $decoded
     */
    protected $decoded = null;

    /**
     * @var ResponseInterface $response
     */
    protected $response;

    /**
     * @var array|null
     */
    protected $request;

    public function __construct(ResponseInterface $response, $request = [])
    {
        $this->response = $response;
        $this->request = $request;
        $this->getBody();
    }

    /**
     * Get body from response
     *
     * @return mixed
     */
    public function getBody()
    {
        if (null === $this->decoded) {
            $this->decoded = json_decode($this->response->getBody(), true);
        }
        return $this->decoded;
    }

    /**
     * Check if response body has key
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $body = $this->getBody();
        return array_has($body, $key);
    }

    /**
     * Check if response is success
     *
     * @param mixed $expect
     * @param string $key
     * @return bool
     */
    public function isSuccess($expect = true, $key = "result")
    {
        return $this->get($key) === $expect;
    }

    /**
     * Get key index from response data
     *
     * @param string $key
     * @param null|mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $body = $this->getBody();
        return array_get($body, $key, $default);
    }

    /**
     * Check if response is success
     *
     * @param int $range_start
     * @param int $range_end
     * @return bool
     */
    public function isOk($range_start = 200, $range_end = 299)
    {
        return $this->getStatusCode() >= $range_start && $this->getStatusCode() <= $range_end;
    }

    /**
     * Get response status code
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * Get headers from response
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * Get RAW response
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
