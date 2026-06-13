<?php

namespace Database\Seeders;

use App\Models\Bpmn;
use App\Models\Project;
use App\Models\System;
use App\Support\DiagramTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class BpmnSeeder extends Seeder
{
    public function run(): void
    {
        $sampleBpmn = File::exists(public_path('bpmn/diagram.bpmn'))
            ? File::get(public_path('bpmn/diagram.bpmn'))
            : $this->minimalBpmnTemplate();

        $crm = System::where('name', 'CRM System')->first();
        $erp = System::where('name', 'ERP System')->first();
        $payment = System::where('name', 'Payment Gateway')->first();

        $crmProject = Project::where('name', 'CRM Modernization')->first();
        $erpProject = Project::where('name', 'ERP Integration Hub')->first();

        if ($crm) {
            Bpmn::updateOrCreate(
                ['name' => 'Customer Onboarding', 'system_id' => $crm->id],
                [
                    'diagram_type' => DiagramTypes::BPMN,
                    'project_id' => $crmProject?->id,
                    'xml' => $sampleBpmn,
                ]
            );

            Bpmn::updateOrCreate(
                ['name' => 'Get Customer Flow', 'system_id' => $crm->id],
                [
                    'diagram_type' => DiagramTypes::SEQUENCE,
                    'project_id' => $crmProject?->id,
                    'xml' => DiagramTypes::defaultSequenceTemplate(),
                ]
            );
        }

        if ($erp) {
            Bpmn::updateOrCreate(
                ['name' => 'Order Processing', 'system_id' => $erp->id],
                [
                    'diagram_type' => DiagramTypes::BPMN,
                    'project_id' => $erpProject?->id,
                    'xml' => $sampleBpmn,
                ]
            );

            Bpmn::updateOrCreate(
                ['name' => 'Process Order Integration', 'system_id' => $erp->id],
                [
                    'diagram_type' => DiagramTypes::SEQUENCE,
                    'project_id' => $erpProject?->id,
                    'xml' => <<<'MERMAID'
sequenceDiagram
    participant CRM as CRM System
    participant ERP as ERP System
    participant Payment as Payment Gateway

    CRM->>ERP: Process Order (SOAP)
    ERP->>Payment: Charge Card (REST)
    Payment-->>ERP: Payment confirmation
    ERP-->>CRM: Order status update
MERMAID,
                ]
            );
        }

        if ($payment) {
            Bpmn::updateOrCreate(
                ['name' => 'Payment Authorization', 'system_id' => $payment->id],
                [
                    'diagram_type' => DiagramTypes::BPMN,
                    'project_id' => null,
                    'xml' => $this->minimalBpmnTemplate('PaymentAuthorization'),
                ]
            );
        }
    }

    private function minimalBpmnTemplate(string $processName = 'SampleProcess'): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<definitions xmlns="http://www.omg.org/spec/BPMN/20100524/MODEL" targetNamespace="http://bpmn.io/schema/bpmn">
  <process id="Process_1" name="{$processName}" isExecutable="false">
    <startEvent id="StartEvent_1" name="Start"/>
    <task id="Task_1" name="Sample task"/>
    <endEvent id="EndEvent_1" name="End"/>
    <sequenceFlow id="Flow_1" sourceRef="StartEvent_1" targetRef="Task_1"/>
    <sequenceFlow id="Flow_2" sourceRef="Task_1" targetRef="EndEvent_1"/>
  </process>
</definitions>
XML;
    }
}
