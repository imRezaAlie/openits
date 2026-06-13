<?php

namespace Database\Seeders;

use App\Models\CanonicalAttribute;
use App\Models\CanonicalEntity;
use App\Models\Domain;
use App\Models\FieldMapping;
use App\Models\PlatformField;
use App\Models\PlatformSchema;
use App\Models\System;
use App\Support\DataLayers;
use Illuminate\Database\Seeder;

class ModernDataStackSeeder extends Seeder
{
    public function run(): void
    {
        $enterpriseDomain = Domain::where('slug', 'enterprise')->first();

        $customer = CanonicalEntity::updateOrCreate(
            ['slug' => 'customer'],
            [
                'name' => 'Customer',
                'domain_id' => $enterpriseDomain?->id,
                'description' => 'Canonical customer entity shared across CRM, ERP, and payment systems',
            ]
        );

        $order = CanonicalEntity::updateOrCreate(
            ['slug' => 'order'],
            [
                'name' => 'Order',
                'domain_id' => $enterpriseDomain?->id,
                'description' => 'Canonical order entity for cross-system order processing',
            ]
        );

        $customerAttrs = $this->seedAttributes($customer, [
            ['name' => 'customer_id', 'data_type' => 'uuid', 'is_required' => true, 'description' => 'Unique customer identifier'],
            ['name' => 'email', 'data_type' => 'string', 'is_required' => true, 'description' => 'Primary email address'],
            ['name' => 'first_name', 'data_type' => 'string', 'description' => 'Given name'],
            ['name' => 'last_name', 'data_type' => 'string', 'description' => 'Family name'],
            ['name' => 'company', 'data_type' => 'string', 'description' => 'Company or organization name'],
            ['name' => 'phone', 'data_type' => 'string', 'description' => 'Primary phone number'],
            ['name' => 'status', 'data_type' => 'string', 'description' => 'Account status (active, inactive, suspended)'],
        ]);

        $orderAttrs = $this->seedAttributes($order, [
            ['name' => 'order_id', 'data_type' => 'uuid', 'is_required' => true, 'description' => 'Unique order identifier'],
            ['name' => 'customer_id', 'data_type' => 'uuid', 'is_required' => true, 'description' => 'Reference to customer'],
            ['name' => 'total_amount', 'data_type' => 'decimal', 'is_required' => true, 'description' => 'Order total in base currency'],
            ['name' => 'currency', 'data_type' => 'string', 'description' => 'ISO 4217 currency code'],
            ['name' => 'status', 'data_type' => 'string', 'description' => 'Order lifecycle status'],
            ['name' => 'created_at', 'data_type' => 'datetime', 'description' => 'Order creation timestamp'],
        ]);

        $crm = System::where('name', 'CRM System')->first();
        $erp = System::where('name', 'ERP System')->first();
        $payment = System::where('name', 'Payment Gateway')->first();

        if ($crm) {
            $this->seedPlatformSchema($crm, 'salesforce-account', 'Salesforce Account', DataLayers::NATIVE, [
                ['native_name' => 'Id', 'native_path' => '$.Id', 'data_type' => 'string', 'is_primary_key' => true],
                ['native_name' => 'Email', 'native_path' => '$.Email', 'data_type' => 'string'],
                ['native_name' => 'FirstName', 'native_path' => '$.FirstName', 'data_type' => 'string'],
                ['native_name' => 'LastName', 'native_path' => '$.LastName', 'data_type' => 'string'],
                ['native_name' => 'AccountName', 'native_path' => '$.Name', 'data_type' => 'string'],
                ['native_name' => 'Phone', 'native_path' => '$.Phone', 'data_type' => 'string'],
                ['native_name' => 'Status__c', 'native_path' => '$.Status__c', 'data_type' => 'string'],
            ], [
                ['field' => 'Id', 'attr' => 'customer_id', 'transform' => 'UUID(normalize(Id))'],
                ['field' => 'Email', 'attr' => 'email'],
                ['field' => 'FirstName', 'attr' => 'first_name'],
                ['field' => 'LastName', 'attr' => 'last_name'],
                ['field' => 'AccountName', 'attr' => 'company'],
                ['field' => 'Phone', 'attr' => 'phone'],
                ['field' => 'Status__c', 'attr' => 'status', 'transform' => 'LOWER(Status__c)'],
            ], $customerAttrs);
        }

        if ($erp) {
            $this->seedPlatformSchema($erp, 'sap-kna1', 'SAP KNA1 (Customer Master)', DataLayers::NATIVE, [
                ['native_name' => 'KUNNR', 'native_path' => '$.KNA1.KUNNR', 'data_type' => 'string', 'is_primary_key' => true],
                ['native_name' => 'SMTP_ADDR', 'native_path' => '$.ADR6.SMTP_ADDR', 'data_type' => 'string'],
                ['native_name' => 'NAME1', 'native_path' => '$.KNA1.NAME1', 'data_type' => 'string'],
                ['native_name' => 'NAME2', 'native_path' => '$.KNA1.NAME2', 'data_type' => 'string'],
                ['native_name' => 'TELF1', 'native_path' => '$.KNA1.TELF1', 'data_type' => 'string'],
                ['native_name' => 'LOEVM', 'native_path' => '$.KNA1.LOEVM', 'data_type' => 'string'],
            ], [
                ['field' => 'KUNNR', 'attr' => 'customer_id', 'transform' => 'padLeft(KUNNR, 10, "0")'],
                ['field' => 'SMTP_ADDR', 'attr' => 'email'],
                ['field' => 'NAME1', 'attr' => 'first_name'],
                ['field' => 'NAME2', 'attr' => 'last_name'],
                ['field' => 'TELF1', 'attr' => 'phone'],
                ['field' => 'LOEVM', 'attr' => 'status', 'transform' => 'IF(LOEVM="X", "inactive", "active")'],
            ], $customerAttrs);

            $this->seedPlatformSchema($erp, 'sap-vbak', 'SAP VBAK (Sales Order)', DataLayers::NATIVE, [
                ['native_name' => 'VBELN', 'native_path' => '$.VBAK.VBELN', 'data_type' => 'string', 'is_primary_key' => true],
                ['native_name' => 'KUNNR', 'native_path' => '$.VBAK.KUNNR', 'data_type' => 'string'],
                ['native_name' => 'NETWR', 'native_path' => '$.VBAK.NETWR', 'data_type' => 'decimal'],
                ['native_name' => 'WAERK', 'native_path' => '$.VBAK.WAERK', 'data_type' => 'string'],
                ['native_name' => 'GBSTK', 'native_path' => '$.VBAK.GBSTK', 'data_type' => 'string'],
                ['native_name' => 'ERDAT', 'native_path' => '$.VBAK.ERDAT', 'data_type' => 'date'],
            ], [
                ['field' => 'VBELN', 'attr' => 'order_id'],
                ['field' => 'KUNNR', 'attr' => 'customer_id'],
                ['field' => 'NETWR', 'attr' => 'total_amount'],
                ['field' => 'WAERK', 'attr' => 'currency'],
                ['field' => 'GBSTK', 'attr' => 'status'],
                ['field' => 'ERDAT', 'attr' => 'created_at'],
            ], $orderAttrs);
        }

        if ($payment) {
            $this->seedPlatformSchema($payment, 'stripe-customer', 'Stripe Customer', DataLayers::NATIVE, [
                ['native_name' => 'id', 'native_path' => '$.id', 'data_type' => 'string', 'is_primary_key' => true],
                ['native_name' => 'email', 'native_path' => '$.email', 'data_type' => 'string'],
                ['native_name' => 'name', 'native_path' => '$.name', 'data_type' => 'string'],
                ['native_name' => 'phone', 'native_path' => '$.phone', 'data_type' => 'string'],
            ], [
                ['field' => 'id', 'attr' => 'customer_id'],
                ['field' => 'email', 'attr' => 'email'],
                ['field' => 'name', 'attr' => 'company'],
                ['field' => 'phone', 'attr' => 'phone'],
            ], $customerAttrs);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $attributeDefs
     * @return array<string, CanonicalAttribute>
     */
    private function seedAttributes(CanonicalEntity $entity, array $attributeDefs): array
    {
        $attrs = [];
        $sort = 0;

        foreach ($attributeDefs as $def) {
            $slug = $def['name'];
            $attrs[$slug] = CanonicalAttribute::updateOrCreate(
                ['canonical_entity_id' => $entity->id, 'slug' => $slug],
                [
                    'name' => $def['name'],
                    'data_type' => $def['data_type'],
                    'description' => $def['description'] ?? null,
                    'is_required' => $def['is_required'] ?? false,
                    'sort_order' => $sort++,
                ]
            );
        }

        return $attrs;
    }

    /**
     * @param  array<int, array<string, mixed>>  $fieldDefs
     * @param  array<int, array<string, mixed>>  $mappingDefs
     * @param  array<string, CanonicalAttribute>  $canonicalAttrs
     */
    private function seedPlatformSchema(
        System $system,
        string $slug,
        string $name,
        string $layer,
        array $fieldDefs,
        array $mappingDefs,
        array $canonicalAttrs
    ): void {
        $schema = PlatformSchema::updateOrCreate(
            ['system_id' => $system->id, 'slug' => $slug],
            [
                'name' => $name,
                'description' => "Native data dictionary for {$system->name}",
                'data_layer' => $layer,
                'source_type' => 'manual',
                'version' => '1.0.0',
            ]
        );

        $fields = [];
        $sort = 0;

        foreach ($fieldDefs as $def) {
            $fields[$def['native_name']] = PlatformField::updateOrCreate(
                ['platform_schema_id' => $schema->id, 'native_name' => $def['native_name']],
                [
                    'native_path' => $def['native_path'] ?? null,
                    'data_type' => $def['data_type'] ?? 'string',
                    'is_primary_key' => $def['is_primary_key'] ?? false,
                    'sort_order' => $sort++,
                ]
            );
        }

        foreach ($mappingDefs as $map) {
            $field = $fields[$map['field']] ?? null;
            $attr = $canonicalAttrs[$map['attr']] ?? null;

            if ($field && $attr) {
                FieldMapping::updateOrCreate(
                    ['platform_field_id' => $field->id, 'canonical_attribute_id' => $attr->id],
                    [
                        'direction' => FieldMapping::DIRECTION_BIDIRECTIONAL,
                        'transform_rule' => $map['transform'] ?? null,
                    ]
                );
            }
        }
    }
}
