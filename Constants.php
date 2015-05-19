<?php

namespace formcorp\sdk;

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