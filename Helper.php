<?php

namespace formcorp\sdk;

use GuzzleHttp\Client;

/**
 * @constant string The url to access the API
 */
defined(__NAMESPACE__ . '\API_URL') or define('API_URL', 'https://api.formcorp.com.au');

/**
 * Class Constants
 * @package formcorp\sdk
 * @author Alex Berriman <alexb@fishvision.com>
 */
class Constants
{
    /**
     * HTTP request methods
     */
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PUT = 'PUT';

    /**
     * @var string The token type
     */
    const TOKEN_TYPE = 'bearer';
}

/**
 * Class FCHelper
 * @package fishvision\formcorpsdk
 * @author Alex Berriman <alexb@fishvision.com>
 */
class Helper
{
    /**
     * @var string The public key for the application
     */
    private $publicKey;

    /**
     * @var string The application secret
     */
    private $secret;

    /**
     * Initialise the object instance.
     * @param $publicKey
     * @param $secret
     */
    public function __construct($publicKey, $secret)
    {
        $this->publicKey = $publicKey;
        $this->secret = $secret;

        // Instantiate the guzzle client
        $this->client = new Client([
            'base_url' => API_URL,
            'headers' => [
                'authorization' => sprintf('%s %s', Constants::TOKEN_TYPE, $publicKey),
            ],
        ]);
    }

    /**
     * Send off an API call.
     * @param $uri
     * @param string $requestMethod
     * @param array $data
     * @return null|void
     */
    public function api($uri, $requestMethod = Constants::METHOD_GET, $data = [])
    {
        // Prepend with a slash
        if (substr($uri, 0, 1) !== '/') {
            $uri = '/' . $uri;
        }

        // Send the request
        switch (strtoupper($requestMethod)) {
            case Constants::METHOD_GET:
                return $this->client->get($uri);

        }

        return null;
    }
}