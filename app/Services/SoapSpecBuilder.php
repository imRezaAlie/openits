<?php

namespace App\Services;

use App\Models\Api;
use App\Models\ApiVersion;

class SoapSpecBuilder
{
    public function build(Api $api, ?ApiVersion $version = null): array
    {
        $version = $version ?? $api->resolveVersion();

        if ($version->soapDetail?->operation_spec) {
            return $version->soapDetail->operation_spec;
        }

        return $this->buildFromFields($api, $version);
    }

    public function buildFromFields(Api $api, ?ApiVersion $version = null): array
    {
        $version = $version ?? $api->resolveVersion();
        $soap = $version->soapDetail;

        return [
            'type' => 'soap',
            'title' => $api->name,
            'description' => $api->description,
            'version' => $version->version,
            'endpoint' => $version->endpoint_url,
            'wsdl_url' => $soap?->wsdl_url,
            'namespace' => $soap?->namespace,
            'method_name' => $soap?->method_name ?? $api->name,
            'soap_action' => $soap?->soap_action,
            'authentication_type' => $version->authentication_type,
            'request_format' => $version->request_format ?? 'XML',
            'response_format' => $version->response_format ?? 'XML',
            'parameters' => $this->defaultParameters($soap?->method_name),
            'request_example' => $this->requestExample($soap),
            'response_example' => $this->responseExample($soap),
        ];
    }

    private function defaultParameters(?string $methodName): array
    {
        return [
            [
                'name' => 'requestBody',
                'in' => 'body',
                'required' => true,
                'type' => 'xml',
                'description' => 'SOAP envelope containing the '.($methodName ?? 'operation').' payload',
            ],
        ];
    }

    private function requestExample($soap): string
    {
        $method = $soap?->method_name ?? 'Operation';
        $namespace = $soap?->namespace ?? 'http://example.com/';

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:tns="{$namespace}">
  <soap:Header/>
  <soap:Body>
    <tns:{$method}>
      <!-- request parameters -->
    </tns:{$method}>
  </soap:Body>
</soap:Envelope>
XML;
    }

    private function responseExample($soap): string
    {
        $method = $soap?->method_name ?? 'Operation';
        $namespace = $soap?->namespace ?? 'http://example.com/';
        $responseMethod = $method.'Response';

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:tns="{$namespace}">
  <soap:Body>
    <tns:{$responseMethod}>
      <!-- response payload -->
    </tns:{$responseMethod}>
  </soap:Body>
</soap:Envelope>
XML;
    }
}
