<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer\Server\Models;


class ErrorModel
{
    /**
     * @var int
     */
    private $errorCode;
    /**
     * @var string|null
     */
    private $errorMessage;
    /**
     * @var string|null
     */
    private $fieldName;

    /**
     * ErrorModel constructor.
     * @param int $errorCode
     * @param null $errorMessage
     * @param null $fieldName
     */
    public function __construct($errorCode = 0, $errorMessage = null, $fieldName = null)
    {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->fieldName = $fieldName;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @param string $fieldName
     */
    public function setFieldName(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $response = [
            'code'    => $this->errorCode,
            'message' => $this->errorMessage
        ];
        if ($this->fieldName !== null) {
            $response['field'] = $this->fieldName;
        }
        return $response;
    }
}
