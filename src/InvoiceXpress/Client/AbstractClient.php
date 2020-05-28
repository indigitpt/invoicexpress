<?php

namespace InvoiceXpress\Client;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractClient
{

    protected $options = [];
    protected $defaultOptions = null;
    protected $host;

    /**
     * @var null|Response
     */
    protected $response = null;
    /**
     * @var self
     */
    protected $client = null;

    /**
     * AbstractClient constructor.
     *
     * @param string $host
     * @param int $timeout
     */
    public function __construct($host = '', $timeout = 30)
    {
        $this->setHost($host);
        $this->options = $this->getDefaultOptions();
        $this->client = $this->getNewClient($timeout);
    }

    /**
     * Get default client options
     *
     * @return array
     */
    abstract protected function getDefaultOptions();

    /**
     * Get Guzzle Client
     * Note: this is an empty client, no proxy is defined yet!
     *
     * @param int $timeout
     * @return GuzzleClient
     */
    public function getNewClient($timeout = 30)
    {
        $handler = new CurlHandler();
        $stack = HandlerStack::create($handler);
        return new GuzzleClient([
            'http_errors' => false,
            'timeout' => $timeout,
            'handler' => $stack
        ]);
    }

    /**
     * Get Host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set host
     *
     * @param string $host
     * @return static
     */
    public function setHost($host)
    {
        $this->host = rtrim($host, '/');
        return $this;
    }

    /**
     * @param string $endpoint
     * @param array $queryData
     * @return Response
     */
    public function post($endpoint, $queryData = [])
    {
        $this->fillQueryData($queryData);
        $this->formParamsToJson();
        $response = $this->request('POST', $endpoint);
        $this->reset();
        return $response;
    }

    /**
     * Fill query string data for request
     *
     * @param array $data
     * @return static
     */
    protected function fillQueryData($data = [])
    {
        foreach ($data as $k => $v) {
            $this->addQuery($k, $v);
        }
        return $this;
    }

    /**
     * Add data to post
     *
     * @param string $key
     * @param mixed $value
     * @param bool $conditional
     * @return static
     */
    public function addQuery($key, $value, $conditional = true)
    {
        if ($conditional) {
            $this->options['query'][$key] = $value;
        }
        return $this;
    }

    /**
     * Converts standard Post into JSON Post
     */
    public function formParamsToJson()
    {
        if (!empty($this->options['form_params'])) {
            # For some reason that i dont know of yet, the json option with guzzle throws a 500 internal server error
            # at InvoiceXpress server. So we will just use a RAW body instead, bit of a hammer, but meh.
            # We have tested also with Postman and seems to be fine with raw body.
            # Guzzle providers json option that will automatically append all the necessary json headers this is a much
            # clean approach, excluding the fact is DOES NOT work :(
            //$this->options['json'] = json_encode($this->options['form_params']);
            $this->options['body'] = json_encode($this->options['form_params']);
            array_forget($this->options, ['form_params']);
        }
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @return Response
     */
    abstract protected function request($method, $endpoint);

    /**
     * Reset request options
     *
     * @return static
     */
    protected function reset()
    {
        $this->options = $this->getDefaultOptions();
        return $this;
    }

    /**
     * @param string $endpoint
     * @param array $queryData
     * @return Response
     */
    public function get($endpoint, $queryData = [])
    {
        $this->fillQueryData($queryData);
        $response = $this->request('GET', $endpoint);
        $this->reset();
        return $response;
    }

    /**
     * @param string $endpoint
     * @param array $queryData
     * @return ResponseInterface
     */
    public function getRaw($endpoint, $queryData = [])
    {
        $this->fillQueryData($queryData);
        $response = $this->requestRaw('GET', $endpoint);
        $this->reset();
        return $response;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @return ResponseInterface
     */
    abstract protected function requestRaw($method, $endpoint);

    /**
     * @param string $endpoint
     * @param array $queryData
     * @return Response
     */
    public function put($endpoint, $queryData = [])
    {
        $this->fillQueryData($queryData);
        $this->formParamsToJson();
        $response = $this->request('PUT', $endpoint);
        $this->reset();
        return $response;
    }

    /**
     * @param string $endpoint
     * @param array $queryData
     * @return Response
     */
    public function delete($endpoint, $queryData = [])
    {
        $this->fillQueryData($queryData);
        $response = $this->request('DELETE', $endpoint);
        $this->reset();
        return $response;
    }

    /**
     * Download file to $path
     *
     * @param string $endpoint
     * @param string $path
     * @return Response
     */
    public function download($endpoint, $path)
    {
        $this->withOptions([
            'sink' => $path
        ]);
        $response = $this->request('GET', $endpoint);
        $this->reset();
        return $response;
    }

    /**
     * Bind options to next request
     *
     * @param array $options
     * @return static
     */
    public function withOptions($options = [])
    {
        foreach (array_dot($options) as $k => $v) {
            array_set($this->options, $k, $v);
        }
        return $this;
    }

    /**
     * Add data to post
     *
     * @param array $data
     * @return static
     */
    public function addPostFromArray($data = [])
    {
        $this->options['form_params'] = $data;
        return $this;
    }

    /**
     * Bind post params to request
     *
     * @param array $post
     * @return static
     */
    public function withPost($post = [])
    {
        foreach ($post as $k => $v) {
            $this->addPost($k, $v);
        }
        return $this;
    }

    /**
     * Add data to post
     *
     * @param string $key
     * @param mixed $value
     * @param bool $conditional
     * @return static
     */
    public function addPost($key, $value, $conditional = true)
    {
        if ($conditional) {
            $this->options['form_params'][$key] = $value;
        }
        return $this;
    }

    /**
     * Bind headers to request
     *
     * @param array $headers
     * @return static
     */
    public function withHeaders($headers)
    {
        return $this->withOptions(['headers' => $headers]);
    }

    /**
     * Unbinds options to next request
     *
     * @param array $options
     * @return static
     */
    public function withoutOptions($options = [])
    {
        foreach (array_dot($options) as $k => $v) {
            array_forget($this->options, $k);
        }
        return $this;
    }

    /**
     * Get the post data
     *
     * @return static
     */
    public function getPostData()
    {
        return $this->options['form_params'];
    }

    /**
     * Get options
     *
     * @return array
     */
    protected function getOptions()
    {
        return $this->options;
    }

    /**
     * Get complete URL
     *
     * @param string $uri
     * @return string
     */
    protected function getURL($uri)
    {
        if (strpos($uri, "http") === 0) {
            return $uri;
        }
        return ltrim($this->host . '/' . ltrim($uri, '/'), '/');
    }

    /**
     * Fill form multipart data for request
     *
     * @param array $body
     * @return static
     */
    protected function fillFormBodyMultipart($body)
    {
        $this->options['multipart'] = $body;
        return $this;
    }
}
