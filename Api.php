<?php

namespace formcorp\sdk;

use GuzzleHttp\Client;

/**
 * @constant string The url to access the API
 */
defined(__NAMESPACE__ . '\API_URL') or define(__NAMESPACE__ . '\API_URL', 'https://api.formcorp.com.au');
//defined(__NAMESPACE__ . '\API_URL') or define(__NAMESPACE__ . '\API_URL', 'http://192.168.247.129:9001/');

/**
 * Class FCHelper
 * @package fishvision\formcorpsdk
 * @author Alex Berriman <aberriman@formcorp.com.au>
 */
class Api
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
    public function call($uri, $requestMethod = Constants::METHOD_GET, $data = [])
    {
        // Prepend with a slash
        if (substr($uri, 0, 1) !== '/') {
            $uri = '/' . $uri;
        }

        // Send the request
        switch (strtoupper($requestMethod)) {
            // Send a GET HTTP request
            case Constants::METHOD_GET:
                $this->result = $this->get($uri, $data);
                break;

            // Send a POST HTTP request
            case Constants::METHOD_POST:
                $this->result = $this->post($uri, $data);
                break;

        }

        // Return a json result
        try {
            return $this->result->json();
        } catch (\Exception $e) {
            return [
                'error' => true,
                'type' => 'exception',
                'message' => 'Unable to decode JSON data',
            ];
        }
    }

    /**
     * @return array|bool
     */
    public function error()
    {
        if (!isset($this->result)) {
            return [
                'error' => true,
                'type' => 'undefined',
                'message' => 'No result defined'
            ];
        }

        // Try to decode the json result
        try {
            $result = $this->result->json();
            if (isset($result['error'])) {
                return $result;
            }
        } catch (\Exception $e) {
            return [
                'error' => true,
                'type' => 'exception',
                'message' => 'Unable to decode JSON data',
            ];
        }

        return false;
    }

    /**
     * @param $uri
     * @param $data
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|mixed|null
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
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|mixed|null
     */
    public function post($uri, $data = [])
    {
        // PHP is loosely typed. Attempt to convert non string elements to string
        foreach ($data as &$val) {
            if (!is_string($val)) {
                $val = strval($val);
            }
        }

        return $this->client->post($uri, [
            'body' => $data,
            'headers' => [
                'Signature' => $this->calculateSignature(Constants::METHOD_POST, $uri, $data),
            ]
        ]);
    }

    /**
     * Generate an access token to the API
     * @return string|bool
     */
    public function generateToken()
    {
        $request = $this->call('v1/auth/token', Constants::METHOD_POST, [
            'timestamp' => time(),
            'nonce' => $this->generateNonce(),
        ]);

        return isset($request['token']) ? $request['token'] : false;
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
        // PHP is loosely typed. Attempt to convert non string elements to string
        foreach ($data as &$val) {
            if (!is_string($val)) {
                $val = strval($val);
            }
        }

        // Calculate the encoded hash using the request
        $plaintext = json_encode([
            'method' => $requestMethod,
            'uri' => $uri,
            'data' => $data,
        ]);

        $plaintext = mb_convert_encoding($plaintext, Constants::SIGNATURE_ENCODING);
        $hash = base64_encode(hash_hmac('sha1', $plaintext, $this->secret));

        return $hash;
    }

    /**
     * @param int $bytes
     * @return string
     */
    private function generateNonce($bytes = 32)
    {
        $nonce = '';
        for ($i = 0; $i < $bytes; ++$i) {
            $nonce .= chr(mt_rand(0, 255));
        }

        return base64_encode($nonce);
    }
}