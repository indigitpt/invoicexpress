<?php

namespace InvoiceXpress\Client;


use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvoiceXpress\Auth;
use InvoiceXpress\Exceptions\InvalidReplacementVariables;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Client
 *
 * @package App\Services\Clients
 */
class Client extends AbstractClient
{
    /**
     * I just dont understand why some urls are append API/ while others are simple non-api
     *
     * @var string $host
     */
    protected $host = 'https://{account_name}.app.invoicexpress.com';

    /**
     * @var string $endpoint
     */
    protected $endpoint;

    /**
     * @var Auth $auth
     */
    protected $auth;

    /**
     * @var integer $timeout
     */
    protected $timeout = 30;

    /**
     * Stores the URL variables to replace further as Key => Value
     * Example : name => jorge
     *
     * @var array $url_variables
     */
    protected $url_variables = [];

    /**
     * InvoiceXpress constructor.
     *
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        parent::__construct($this->host, $this->timeout);
        $this->auth($auth);
    }

    /**
     * Defines the auth for the client in order to replace the variables
     *
     * @param Auth $auth
     * @return $this
     */
    public function auth(Auth $auth)
    {
        $this->auth = $auth;
        return $this;
    }

    /**
     * @return array|null
     */
    protected function getDefaultOptions()
    {
        if (null === $this->defaultOptions) {
            $this->defaultOptions = [
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8'
                ],
                'allow_redirects' => [
                    'max' => 10,        // allow at most 10 redirects.
                    //'strict' => true,      // use "strict" RFC compliant redirects.
                    'referer' => true,      // add a Referer header
                    //'protocols' => ['https'], // only allow https URLs
                    'track_redirects' => true
                ]
            ];
        }
        return $this->defaultOptions;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @return Response
     * @throws
     */
    protected function request($method, $endpoint)
    {
        try {
            $endpoint = $this->getURL($endpoint);
            $this->endpoint = $endpoint;
            $options = $this->getOptions();
            $request = $this->client->request($method, $endpoint, $options);
            return new Response($request, compact('method', 'endpoint', 'options'));
        } catch (Exception | GuzzleException $e) {
            # TODO : Add more error handling here
            # Right now we will just rethrow it
            throw $e;
        }
    }

    /**
     * Get complete URL
     *
     * @param string $uri
     * @return string
     * @throws InvalidReplacementVariables
     */
    protected function getURL($uri)
    {
        $final_uri = ltrim($this->host . '/' . ltrim($uri, '/'), '/');

        # If there is auth provided, we should take the necessary steps here to inject them.
        if ($this->auth !== null) {
            $this->addUrlVariable('account_name', $this->auth->getAccountName());
            $this->addQuery('api_key', $this->auth->getApiKey());
        }

        # Replace all the URL variables before we call.
        return $this->replaceUrlVariables($this->getUrlVariables(), $final_uri);
    }

    /**
     * Adds URL variables for replacement.
     *
     * @param $key
     * @param $value
     * @return Client
     */
    public function addUrlVariable($key, $value)
    {
        $this->url_variables[$key] = $value;
        return $this;
    }

    /**
     * Make sure to replace any URL elements with the given key value pairs.
     *
     * @param array $replaceKeys
     * @param string $url
     * @return string
     * @throws InvalidReplacementVariables
     */
    private function replaceUrlVariables(array $replaceKeys, $url)
    {
        $given = [];
        foreach ($replaceKeys as $k => $v) {
            $given['{' . $k . '}'] = $v;
        }
        $given = array_filter($given);
        $count_expected = preg_match_all('/{+(.*?)}/', $url, $matches);
        $out = str_ireplace(array_keys($given), $given, $url);
        if (count($given) < $count_expected) {
            throw new InvalidReplacementVariables($given, $matches, $url);
        }

        return $out;
    }

    /**
     * @return array
     */
    public function getUrlVariables()
    {
        return $this->url_variables;
    }

    /**
     * @param $method
     * @param $endpoint
     * @return mixed|ResponseInterface
     * @throws
     */
    protected function requestRaw($method, $endpoint)
    {
        $endpoint = $this->getURL($endpoint);
        return $this->client->request($method, $endpoint, $this->getOptions());
    }
}
