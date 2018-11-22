<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks-pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Errors;


class ServerErrors
{
    const FIELD_REQUIRED = 99994001;
    const TYPE_REQUIRED = 99994002;

    /* 404 */
    const NO_CONTROLLER_MATCHING_URI_CODE = 99994041;
    const NO_METHOD_MATCHING_URI_CODE = 99994042;

    /* 500 */
    const CONTROLLER_NOT_FOUND_CODE = 99995001;
    const METHOD_MAP_FILE_MISSING_CODE = 99995002;
    const METHOD_MAP_FILE_DECODING_ERROR_CODE = 99995003;
    const DEFAULT_AUTH_PROVIDER_NOT_SET_CODE = 99995004;
    const AUTH_PROVIDER_CLASS_DOES_NOT_EXIST_CODE = 99995005;
    const AUTH_PROVIDER_CLASS_INSTANCE_ERROR_CODE = 99995006;
    const MAP_BUILDER_EXCEPTION_CODE = 99995007;
    const MAP_BUILDER_ENCODING_ERROR_CODE = 99995008;
}
