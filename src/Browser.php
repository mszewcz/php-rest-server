<?php
/**
 * REST server implementation for PHP
 *
 * @author      Michal Szewczyk <ms@msworks.pl>
 * @copyright   Michal Szewczyk
 * @license     MIT
 */
declare(strict_types=1);

namespace MS\RestServer;

use MS\LightFramework\Html\Tags;
use MS\RestServer\Browser\ModelDescriber;
use MS\RestServer\Server\Helpers\DataTypeHelper;


class Browser
{
    /**
     * @var Base
     */
    private $base;
    /**
     * @var DataTypeHelper
     */
    private $dataTypeHelper;

    /**
     * Browser constructor.
     */
    public function __construct()
    {
        $this->base = Base::getInstance();
        $this->dataTypeHelper = new DataTypeHelper();
    }

    /**
     * Displays API Browser
     */
    public function display(): string
    {
        $head = [
            Tags::title('API Browser'),
            Tags::link(
                '',
                ['type' => 'text/css', 'rel' => 'stylesheet', 'href' => '/assets/css/browser.css']
            )
        ];
        $body = [
            Tags::header('API Browser'),
            Tags::div($this->listControllers(), ['class' => 'wrapper']),
            Tags::script('', ['type' => 'text/javascript', 'src' => '/assets/js/jquery.min.js']),
            Tags::script('', ['type' => 'text/javascript', 'src' => '/assets/js/browser.js']),
        ];
        $html = [
            Tags::doctype('html'),
            Tags::head(implode(Tags::CRLF, $head)),
            Tags::body(implode(Tags::CRLF, $body))
        ];
        $output = Tags::html(implode(Tags::CRLF, $html));

        return $output;
    }

    /**
     * Returns list of controllers
     *
     * @return string
     */
    private function listControllers(): string
    {
        $controllers = $this->base->getControllers();
        $definitionsDir = $this->base->getDefinitionsDir();

        $ret = [];
        foreach ($controllers as $controller) {
            $controllerN = Tags::span((string) $controller->name, ['class' => 'name']);
            $controllerEp = [];
            $showE = Tags::span('Expand controller', ['class' => 'exp-c']);
            $hideE = Tags::span('Collapse controller', ['class' => 'col-c']);
            $showED = Tags::span('Expand endpoints', ['class' => 'exp-e']);
            $hideED = Tags::span('Collapse endpoints', ['class' => 'col-e']);

            foreach ($controller->endpoints as $endpoint) {
                $mapFile = $this->base->getSafeFileName((string) $endpoint->uri);
                $mapFilePath = sprintf('%s%s.json', $definitionsDir, $mapFile);
                $controllerEp[] = $this->listEndpoints($mapFilePath);
            }

            $controller = [
                Tags::div($controllerN . $showE . $hideE . $showED . $hideED, ['class' => 'controller__heading']),
                Tags::div(implode(Tags::CRLF, $controllerEp), ['class' => 'controller__endpoints'])
            ];

            $ret[] = Tags::div(implode(Tags::CRLF, $controller), ['class' => 'controller']);
        }
        return implode(Tags::CRLF, $ret);
    }

    /**
     * Returns list of controller's endpoints
     *
     * @param string $controllerMapFile
     * @return string
     * @codeCoverageIgnore
     */
    private function listEndpoints(string $controllerMapFile): string
    {
        $ret = '';
        try {
            $controllerMap = $this->base->decodeAsArray($this->base->fileRead($controllerMapFile));

            foreach ($controllerMap as $endpointData) {
                if ($endpointData['endpointHidden'] === false) {
                    $authIcon = '';
                    if ($endpointData['endpointAuthProvider'] !== 'none') {
                        $authIcon = Tags::i(
                            '!',
                            ['class' => 'auth-required', 'title' => 'Authorization required']
                        );
                    }

                    $endpointHeading = [];
                    $endpointHeading[] = Tags::div($endpointData['endpointHttpMethod'], ['class' => 'http-method']);
                    $endpointHeading[] = Tags::div($endpointData['endpointUri'] . $authIcon, ['class' => 'uri']);

                    $request = [];
                    $request[] = Tags::h2('Request');
                    $request[] = $this->showRequestSpecification($endpointData);

                    $response = [];
                    $response[] = Tags::h2('Responses');
                    $response[] = $this->showResponseSpecification($endpointData);

                    $endpointDetails = [];
                    if ($endpointData['endpointDesc'] !== '') {
                        $endpointDetails[] = Tags::div($endpointData['endpointDesc'], ['class' => 'description']);
                    }
                    $endpointDetails[] = Tags::div(implode(Tags::CRLF, $request), ['class' => 'request']);
                    $endpointDetails[] = Tags::div(implode(Tags::CRLF, $response), ['class' => 'response']);

                    $endpoint = [];
                    $endpoint[] = Tags::div(
                        implode(Tags::CRLF, $endpointHeading),
                        ['class' => 'endpoint__heading']
                    );
                    $endpoint[] = Tags::div(
                        implode(Tags::CRLF, $endpointDetails),
                        ['class' => 'endpoint__details']
                    );
                    $ret .= Tags::div(
                        implode(Tags::CRLF, $endpoint),
                        ['class' => 'endpoint endpoint-' . $endpointData['endpointHttpMethod']]);
                }
            }
        } catch (\Exception $e) {
        }

        return $ret;
    }

    /**
     * Returns endpoint's request specification
     *
     * @param array $endpointData
     * @return string
     * @codeCoverageIgnore
     */
    private function showRequestSpecification(array $endpointData): string
    {
        $requestSpec = [];

        $row = [];
        $row[] = Tags::div('Parameter', ['class' => 'p-name']);
        $row[] = Tags::div('Type', ['class' => 'p-type']);
        $row[] = Tags::div('Required', ['class' => 'p-required']);
        $row[] = Tags::div('Data type', ['class' => 'p-data-type']);
        $requestSpec[] = Tags::div(implode(Tags::CRLF, $row), ['class' => 'row header']);

        if (count($endpointData['endpointParams']) === 0) {
            $row = [];
            $row[] = Tags::div('-', ['class' => 'p-name']);
            $row[] = Tags::div('-', ['class' => 'p-type']);
            $row[] = Tags::div('-', ['class' => 'p-required']);
            $row[] = Tags::div('-', ['class' => 'p-data-type']);
            $requestSpec[] = Tags::div(implode(Tags::CRLF, $row), ['class' => 'row']);

            return Tags::div(implode(Tags::CRLF, $requestSpec), ['class' => 'table']);
        }

        $requestSpec[] = $this->showInputPathSpecification($endpointData);
        $requestSpec[] = $this->showInputQuerySpecification($endpointData);
        $requestSpec[] = $this->showInputBodySpecification($endpointData);

        return Tags::div(implode(Tags::CRLF, $requestSpec), ['class' => 'table']);
    }

    /**
     * @param array $endpointData
     * @return string
     * @codeCoverageIgnore
     */
    private function showInputPathSpecification(array $endpointData): string
    {
        $ret = [];
        if (isset($endpointData['endpointParams']['path'])) {
            foreach ($endpointData['endpointParams']['path'] as $inputPath) {
                $paramName = $inputPath['paramName'];
                $paramType = 'path';
                $paramDataType = $this->dataTypeHelper->getDataType($inputPath['paramType']);
                $paramRequired = $inputPath['paramRequired'] === true ? 'Y' : 'N';

                $row = [];
                $row[] = Tags::div($paramName, ['class' => 'p-name']);
                $row[] = Tags::div($paramType, ['class' => 'p-type']);
                $row[] = Tags::div($paramRequired, ['class' => 'p-required']);
                $row[] = Tags::div($paramDataType, ['class' => 'p-data-type']);
                $ret[] = Tags::div(implode(Tags::CRLF, $row), ['class' => 'row']);
            }
        }
        return implode(Tags::CRLF, $ret);
    }

    /**
     * @param array $endpointData
     * @return string
     * @codeCoverageIgnore
     */
    private function showInputQuerySpecification(array $endpointData): string
    {
        $ret = [];
        if (isset($endpointData['endpointParams']['query'])) {
            foreach ($endpointData['endpointParams']['query'] as $inputQuery) {
                $paramName = $inputQuery['paramName'];
                $paramType = 'query';
                $paramDataType = $this->dataTypeHelper->getDataType($inputQuery['paramType']);
                $paramRequired = $inputQuery['paramRequired'] === true ? 'Y' : 'N';

                $row = [];
                $row[] = Tags::div($paramName, ['class' => 'p-name']);
                $row[] = Tags::div($paramType, ['class' => 'p-type']);
                $row[] = Tags::div($paramRequired, ['class' => 'p-required']);
                $row[] = Tags::div($paramDataType, ['class' => 'p-data-type']);
                $ret[] = Tags::div(implode(Tags::CRLF, $row), ['class' => 'row']);
            }
        }
        return implode(Tags::CRLF, $ret);
    }

    /**
     * @param array $endpointData
     * @return string
     * @codeCoverageIgnore
     */
    private function showInputBodySpecification(array $endpointData): string
    {
        $ret = [];
        if (isset($endpointData['endpointParams']['body'])) {
            foreach ($endpointData['endpointParams']['body'] as $inputBody) {
                $paramName = $endpointData['endpointHttpMethod'] === 'get' ? 'body' : '-';
                $paramType = $endpointData['endpointHttpMethod'] === 'get' ? 'query' : 'body';
                $paramDataType = $this->dataTypeHelper->getDataType($inputBody['paramType']);
                if ($this->dataTypeHelper->isModelType($inputBody['paramType'])) {
                    $paramDataType = $this->describeModel($inputBody['paramType']);
                }
                $paramRequired = $inputBody['paramRequired'] === true ? 'Y' : 'N';

                $row = [];
                $row[] = Tags::div($paramName, ['class' => 'p-name']);
                $row[] = Tags::div($paramType, ['class' => 'p-type']);
                $row[] = Tags::div($paramRequired, ['class' => 'p-required']);
                $row[] = Tags::div($paramDataType, ['class' => 'p-data-type']);
                $ret[] = Tags::div(implode(Tags::CRLF, $row), ['class' => 'row']);
            }
        }
        return implode(Tags::CRLF, $ret);
    }

    /**
     * Returns endpoint's response specification
     *
     * @param array $endpointData
     * @return string
     * @codeCoverageIgnore
     */
    private function showResponseSpecification(array $endpointData): string
    {
        $responseSpec = [];

        $row = [];
        $row[] = Tags::div('HTTP status code', ['class' => 'p-code']);
        $row[] = Tags::div('Reason', ['class' => 'p-reason']);
        $row[] = Tags::div('Data type', ['class' => 'p-data-type']);
        $responseSpec[] = Tags::div(implode(Tags::CRLF, $row), ['class' => 'row header']);

        if (count($endpointData['endpointResponses']) === 0) {
            $row = [];
            $row[] = Tags::div('-', ['class' => 'p-code']);
            $row[] = Tags::div('-', ['class' => 'p-reason']);
            $row[] = Tags::div('-', ['class' => 'p-data-type']);
            $responseSpec[] = Tags::div(implode(Tags::CRLF, $row), ['class' => 'row']);

            return Tags::div(implode(Tags::CRLF, $responseSpec), ['class' => 'table']);
        }

        foreach ($endpointData['endpointResponses'] as $responseCode => $responseBody) {
            $responseReason = $responseBody;
            $paramDataType = '-';

            if ($responseCode === 200) {
                $responseReason = '-';
                $paramDataType = $this->dataTypeHelper->getDataType($responseBody);
                if ($this->dataTypeHelper->isModelType($paramDataType)) {
                    $paramDataType = $this->describeModel($responseBody);
                }
            }

            $row = [];
            $row[] = Tags::div($responseCode, ['class' => 'p-code']);
            $row[] = Tags::div($responseReason, ['class' => 'p-reason']);
            $row[] = Tags::div($paramDataType, ['class' => 'p-data-type']);
            $responseSpec[] = Tags::div(implode(Tags::CRLF, $row), ['class' => 'row']);
        }

        return Tags::div(implode(Tags::CRLF, $responseSpec), ['class' => 'table']);
    }


    /**
     * Describes data model
     *
     * @param string $paramType
     * @return string
     */
    private function describeModel(string $paramType): string
    {
        $modelDescriber = new ModelDescriber();
        $describedModels = $modelDescriber->describeModel($paramType);
        $ret = [];

        foreach ($describedModels as $describedModelName => $describedModelProps) {
            $ret[] = Tags::div(sprintf('%s {', Tags::b($describedModelName)), ['class' => 'm-name']);

            foreach ($describedModelProps as $describedModelProp) {
                $propName = $describedModelProp['propertyName'];
                $propType = $describedModelProp['propertyType'];
                $propOpt = $describedModelProp['propertyOptional'] ? '?' : '';
                $propDesc = $describedModelProp['propertyName'] !== ''
                    ? sprintf('%s%s: %s;', Tags::b($propName), $propOpt, $propType)
                    : sprintf('%s', $propType);

                $ret[] = Tags::div($propDesc, ['class' => 'm-prop']);
            }

            $ret[] = Tags::div('}', ['class' => 'm-close']);
        }
        return implode(Tags::CRLF, $ret);
    }
}
