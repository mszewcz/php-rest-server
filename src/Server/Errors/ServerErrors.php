<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Errors;


class ServerErrors
{
    /* 404 */
    const NO_CONTROLLER_MATCHING_URI_CODE = 40400;
    const NO_CONTROLLER_MATCHING_URI_MESSAGE = 'No controller/method matching uri: %s';
    const NO_METHOD_MATCHING_URI_CODE = 40401;
    const NO_METHOD_MATCHING_URI_MESSAGE = 'No method matching uri: %s';

    /* 500 */
    const CONTROLLER_NOT_FOUND_CODE = 50000;
    const CONTROLLER_NOT_FOUND_MESSAGE = 'Controller class not found';
    const METHOD_MAP_FILE_MISSING_CODE = 50001;
    const METHOD_MAP_FILE_MISSING_MESSAGE = 'Method map file is missing for uri: %s';
    const METHOD_MAP_FILE_DECODING_ERROR_CODE = 50002;
    const METHOD_MAP_FILE_DECODING_ERROR_MESSAGE = 'Method map file decoding error for uri: %s';
    const DEFAULT_AUTH_PROVIDER_NOT_SET_CODE = 50003;
    const DEFAULT_AUTH_PROVIDER_NOT_SET_MESSAGE = 'Default auth provider is not set';
    const AUTH_PROVIDER_CLASS_DOES_NOT_EXIST_CODE = 50004;
    const AUTH_PROVIDER_CLASS_DOES_NOT_EXIST_MESSAGE = 'Auth provider class (%s) does not exist';
    const AUTH_PROVIDER_CLASS_INSTANCE_ERROR_CODE = 50005;
    const AUTH_PROVIDER_CLASS_INSTANCE_ERROR_MESSAGE = 'Auth provider class (%s) has to be an instance of AbstractAuthProvider';
    const MAP_BUILDER_EXCEPTION_CODE = 50006;
    const MAP_BUILDER_EXCEPTION_MESSAGE = '';
    const MAP_BUILDER_ENCODING_ERROR_CODE = 50007;
    const MAP_BUILDER_ENCODING_ERROR_MESSAGE = 'Map encoding error';
}
