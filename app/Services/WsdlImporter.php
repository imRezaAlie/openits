<?php

namespace App\Services;

use App\Models\Api;
use App\Models\ApiVersion;
use App\Models\SoapDetail;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class WsdlImporter
{
    /**
     * @return array<int, Api>
     */
    public function import(UploadedFile|string $source, ?string $wsdlUrl = null): array
    {
        $content = $source instanceof UploadedFile
            ? file_get_contents($source->getRealPath())
            : $source;

        $url = $wsdlUrl ?? ($source instanceof UploadedFile ? $source->getClientOriginalName() : 'imported.wsdl');

        $dom = new DOMDocument;
        if (! @$dom->loadXML($content)) {
            throw new \InvalidArgumentException('Invalid WSDL XML document.');
        }

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('wsdl', 'http://schemas.xmlsoap.org/wsdl/');
        $xpath->registerNamespace('soap', 'http://schemas.xmlsoap.org/wsdl/soap/');

        $targetNamespace = $dom->documentElement->getAttribute('targetNamespace') ?: null;
        $serviceLocation = $this->extractServiceLocation($xpath);
        $operations = $this->extractOperations($xpath, $dom);
        $created = [];

        DB::transaction(function () use ($operations, $url, $targetNamespace, $serviceLocation, &$created) {
            foreach ($operations as $operation) {
                $api = Api::create([
                    'name' => $operation['name'],
                    'type' => 'soap',
                    'description' => 'SOAP operation: '.$operation['name'],
                ]);

                $version = ApiVersion::create([
                    'api_id' => $api->id,
                    'version' => '1.0.0',
                    'endpoint_url' => $serviceLocation,
                    'request_format' => 'XML',
                    'response_format' => 'XML',
                    'status' => 'active',
                    'is_default' => true,
                ]);

                SoapDetail::create([
                    'api_version_id' => $version->id,
                    'wsdl_url' => $url,
                    'namespace' => $targetNamespace,
                    'soap_action' => $operation['soap_action'],
                    'method_name' => $operation['name'],
                ]);

                $created[] = $api;
            }
        });

        if (empty($created)) {
            $api = Api::create([
                'name' => 'SOAP Service',
                'type' => 'soap',
                'description' => 'Imported from WSDL',
            ]);

            $version = ApiVersion::create([
                'api_id' => $api->id,
                'version' => '1.0.0',
                'endpoint_url' => $serviceLocation,
                'request_format' => 'XML',
                'response_format' => 'XML',
                'status' => 'active',
                'is_default' => true,
            ]);

            SoapDetail::create([
                'api_version_id' => $version->id,
                'wsdl_url' => $url,
                'namespace' => $targetNamespace,
            ]);

            $created[] = $api;
        }

        return $created;
    }

    private function extractServiceLocation(DOMXPath $xpath): ?string
    {
        $nodes = $xpath->query('//soap:address/@location | //*[local-name()="address"]/@location');

        if ($nodes && $nodes->length > 0) {
            return $nodes->item(0)->nodeValue;
        }

        return null;
    }

    /**
     * @return array<int, array{name: string, soap_action: ?string}>
     */
    private function extractOperations(DOMXPath $xpath, DOMDocument $dom): array
    {
        $operations = [];
        $portTypeNodes = $xpath->query('//wsdl:portType/wsdl:operation');

        if (! $portTypeNodes || $portTypeNodes->length === 0) {
            $portTypeNodes = $xpath->query('//*[local-name()="portType"]/*[local-name()="operation"]');
        }

        if ($portTypeNodes) {
            foreach ($portTypeNodes as $node) {
                $name = $node->getAttribute('name');
                if ($name) {
                    $operations[$name] = [
                        'name' => $name,
                        'soap_action' => $this->findSoapAction($xpath, $name),
                    ];
                }
            }
        }

        $bindingNodes = $xpath->query('//wsdl:binding/wsdl:operation');
        if ($bindingNodes) {
            foreach ($bindingNodes as $node) {
                $name = $node->getAttribute('name');
                if ($name && ! isset($operations[$name])) {
                    $operations[$name] = [
                        'name' => $name,
                        'soap_action' => $this->findSoapAction($xpath, $name),
                    ];
                }
            }
        }

        return array_values($operations);
    }

    private function findSoapAction(DOMXPath $xpath, string $operationName): ?string
    {
        $query = "//wsdl:binding/wsdl:operation[@name='{$operationName}']/soap:operation/@soapAction";
        $nodes = $xpath->query($query);

        if ($nodes && $nodes->length > 0) {
            return $nodes->item(0)->nodeValue;
        }

        $fallback = $xpath->query("//*[local-name()='operation'][@name='{$operationName}']/*[local-name()='operation']/@soapAction");

        if ($fallback && $fallback->length > 0) {
            return $fallback->item(0)->nodeValue;
        }

        return null;
    }
}
