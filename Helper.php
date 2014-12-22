<?php

namespace formcorp\sdk;

use GuzzleHttp\Client;

/**
 * @constant string The url to access the API
 */
defined(__NAMESPACE__ . '\API_URL') or define(__NAMESPACE__ . '\API_URL', 'https://api.formcorp.com.au');

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
    const TOKEN_TYPE = 'Bearer';
    const SIGNATURE_ENCODING = 'UTF-8';
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
        ]);

        // Set the identifying token
        $this->client->setDefaultOption('headers/Authorization', sprintf('%s %s', Constants::TOKEN_TYPE, $publicKey));
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
            // Send a GET HTTP request
            case Constants::METHOD_GET:
                return $this->get($uri, $data);

            // Send a POST HTTP request
            case Constants::METHOD_POST:
                return $this->post($uri, $data);

        }

        return null;
    }

    /**
     * @param $uri
     * @param $data
     */
    public function get($uri, $data)
    {
        return $this->client->get($uri, [
            'headers' => [
                'Signature' => $this->calculateSignature(Constants::METHOD_GET, $uri),
            ],
        ]);
    }

    /**
     * @param $uri
     * @param array $data
     */
    public function post($uri, $data = [])
    {
        return $this->client->post($uri, [
            'body' => $data,
            'headers' => [
                'Signature' => $this->calculateSignature(Constants::METHOD_POST, $uri, $data),
            ]
        ]);
    }

    /**
     * Calculates the signature to send through with the API request.
     * @param $requestMethod
     * @param $uri
     * @param array $data
     * @return string
     */
    private function calculateSignature($requestMethod, $uri, $data = [])
    {
        // Calculate the encoded hash using the request
        $plaintext = json_encode([
            'method' => $requestMethod,
            'uri' => $uri,
            'data' => $data,
        ]);
        var_dump($plaintext);

        $plaintext = mb_convert_encoding($plaintext, Constants::SIGNATURE_ENCODING);
        $hash = base64_encode(hash_hmac('sha1', $plaintext, $this->secret));

        return $hash;
    }
}