<?php

namespace fishvision\formcorpsdk;

/**
 * Class FCHelper
 * @package fishvision\formcorpsdk
 * @author Alex Berriman <alexb@fishvision.com>
 */
class FCHelper
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
    }
}