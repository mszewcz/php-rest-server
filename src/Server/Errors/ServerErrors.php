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
    const NO_CONTROLLER_MATCHING_URI_CODE = 1004041;
    const NO_METHOD_MATCHING_URI_CODE = 1004042;

    /* 500 */
    const CONTROLLER_NOT_FOUND_CODE = 1005001;
    const METHOD_MAP_FILE_MISSING_CODE = 1005002;
    const METHOD_MAP_FILE_DECODING_ERROR_CODE = 1005003;
    const DEFAULT_AUTH_PROVIDER_NOT_SET_CODE = 1005004;
    const AUTH_PROVIDER_CLASS_DOES_NOT_EXIST_CODE = 1005005;
    const AUTH_PROVIDER_CLASS_INSTANCE_ERROR_CODE = 1005006;
    const MAP_BUILDER_EXCEPTION_CODE = 1005007;
    const MAP_BUILDER_ENCODING_ERROR_CODE = 1005008;
}
