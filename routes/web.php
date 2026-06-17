<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ApiVersionController;
use App\Http\Controllers\BpmnController;
use App\Http\Controllers\CanonicalEntityController;
use App\Http\Controllers\DataStackController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\FieldMappingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\PlatformSchemaController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\SystemDocumentController;
use App\Http\Controllers\TechnologyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/forgetpasswd', function () {
    return view('auth.passwords.forgetpasswd');
});

Route::post('/run-deployment', function () {
    config(['session.driver' => 'file']);
    Schema::defaultStringLength(191);

    try {
        Artisan::call('migrate', ['--force' => true]);
        $migrateOutput = Artisan::output();

        Artisan::call('db:seed', ['--force' => true]);
        $seedOutput = Artisan::output();

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return response()->json([
            'status' => 'success',
            'message' => 'Migrations and seeders ran successfully.',
            'migrate_output' => $migrateOutput,
            'seed_output' => $seedOutput,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
})->middleware(['deployment.auth', 'throttle:3,1']);

Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::resource('supplier', VendorController::class);
    Route::resource('project', ProjectController::class);

    Route::get('processes', [BpmnController::class, 'catalog'])->name('processes.index');
    Route::get('infrastructure', [ServerController::class, 'catalog'])->name('infrastructure.index');
    Route::get('documents', [SystemDocumentController::class, 'catalog'])->name('documents.index');
    Route::get('systems/{system}/processes', [BpmnController::class, 'index'])->name('systems.processes');
    Route::get('technologies', [TechnologyController::class, 'catalog'])->name('technologies.index');
    Route::post('technologies', [TechnologyController::class, 'store'])->name('technologies.store');
    Route::get('technologies/{technology}', [TechnologyController::class, 'show'])->name('technologies.show');
    Route::put('technologies/{technology}', [TechnologyController::class, 'update'])->name('technologies.update');
    Route::delete('technologies/{technology}', [TechnologyController::class, 'destroy'])->name('technologies.destroy');
    Route::get('systems/{system}/technologies', [TechnologyController::class, 'index'])->name('systems.technologies');
    Route::post('systems/{system}/technologies', [TechnologyController::class, 'sync'])->name('systems.technologies.sync');
    Route::get('systems/{system}/servers', [ServerController::class, 'index'])->name('systems.servers');
    Route::post('systems/{system}/servers', [ServerController::class, 'store'])->name('systems.servers.store');
    Route::put('systems/{system}/servers/{server}', [ServerController::class, 'update'])->name('systems.servers.update');
    Route::delete('systems/{system}/servers/{server}', [ServerController::class, 'destroy'])->name('systems.servers.destroy');
    Route::get('systems/{system}/documents', [SystemDocumentController::class, 'index'])->name('systems.documents');
    Route::get('systems/{system}/documents/create-markdown', [SystemDocumentController::class, 'createMarkdown'])->name('systems.documents.create-markdown');
    Route::post('systems/{system}/documents/markdown', [SystemDocumentController::class, 'storeMarkdown'])->name('systems.documents.store-markdown');
    Route::post('systems/{system}/documents/generate', [SystemDocumentController::class, 'generate'])->name('systems.documents.generate');
    Route::get('systems/{system}/documents/preview/{type}', [SystemDocumentController::class, 'preview'])->name('systems.documents.preview');
    Route::get('systems/{system}/documents/{systemDocument}/edit', [SystemDocumentController::class, 'editMarkdown'])->name('systems.documents.edit-markdown');
    Route::put('systems/{system}/documents/{systemDocument}/markdown', [SystemDocumentController::class, 'updateMarkdown'])->name('systems.documents.update-markdown');
    Route::get('systems/{system}/documents/{systemDocument}/view', [SystemDocumentController::class, 'view'])->name('systems.documents.view');
    Route::post('systems/{system}/documents', [SystemDocumentController::class, 'store'])->name('systems.documents.store');
    Route::put('systems/{system}/documents/{systemDocument}', [SystemDocumentController::class, 'update'])->name('systems.documents.update');
    Route::delete('systems/{system}/documents/{systemDocument}', [SystemDocumentController::class, 'destroy'])->name('systems.documents.destroy');
    Route::get('systems/{system}/documents/{systemDocument}/download', [SystemDocumentController::class, 'download'])->name('systems.documents.download');
    Route::get('systems/bpmn/{system}/create', [BpmnController::class, 'create'])->name('systems.create.bpmn');
    Route::get('systems/sequence/{system}/create', [BpmnController::class, 'createSequence'])->name('systems.create.sequence');
    Route::get('systems/bpmn/{bpmn}/show', [BpmnController::class, 'show'])->name('systems.bpmn.show');
    Route::get('systems/sequence/{bpmn}/show', [BpmnController::class, 'showSequence'])->name('systems.sequence.show');
    Route::put('systems/bpmn/{bpmn}', [BpmnController::class, 'update'])->name('systems.update.bpmn');
    Route::delete('systems/bpmn/{bpmn}', [BpmnController::class, 'destroy'])->name('systems.destroy.bpmn');
    Route::post('systems/bpmn', [BpmnController::class, 'store'])->name('systems.store.bpmn');

    Route::resource('user', UserController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('apis', ApiController::class);
    Route::post('apis/import', [ApiController::class, 'import'])->name('apis.import');
    Route::get('apis/{api}/spec', [ApiController::class, 'spec'])->name('apis.spec');
    Route::post('apis/{api}/tps', [ApiController::class, 'addTps'])->name('apis.addTps');
    Route::post('apis/{api}/versions', [ApiVersionController::class, 'store'])->name('apis.versions.store');
    Route::put('apis/{api}/versions/{version}', [ApiVersionController::class, 'update'])->name('apis.versions.update');
    Route::delete('apis/{api}/versions/{version}', [ApiVersionController::class, 'destroy'])->name('apis.versions.destroy');
    Route::post('apis/{api}/versions/{version}/default', [ApiVersionController::class, 'setDefault'])->name('apis.versions.setDefault');
    Route::get('apis/{api}/systems', [ApiController::class, 'getSystems'])->name('apis.getSystems');
    Route::post('apis/{api}/systems', [ApiController::class, 'attachSystem'])->name('apis.attachSystem');
    Route::delete('apis/{api}/systems/{system}', [ApiController::class, 'detachSystem'])->name('apis.detachSystem');

    Route::get('systems', [SystemController::class, 'index'])->name('systems.index');
    Route::post('systems', [SystemController::class, 'store'])->name('systems.store');
    Route::put('systems/{system}', [SystemController::class, 'update'])->name('systems.update');
    Route::delete('systems/{system}', [SystemController::class, 'destroy'])->name('systems.destroy');

    Route::get('domains', [DomainController::class, 'index'])->name('domains.index');
    Route::post('domains', [DomainController::class, 'store'])->name('domains.store');
    Route::get('domains/{domain}', [DomainController::class, 'show'])->name('domains.show');
    Route::put('domains/{domain}', [DomainController::class, 'update'])->name('domains.update');
    Route::delete('domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');

    Route::get('integrations/catalog', [IntegrationController::class, 'catalog'])->name('integrations.catalog');
    Route::get('integrations/catalog/export', [IntegrationController::class, 'exportCatalog'])->name('integrations.catalog.export');
    Route::get('integrations/export', [IntegrationController::class, 'exportLandscape'])->name('integrations.export');
    Route::get('integrations/tree', [IntegrationController::class, 'tree'])->name('integrations.tree');
    Route::get('integrations/tree/data', [IntegrationController::class, 'treeData'])->name('integrations.tree.data');
    Route::get('integrations/systems/{system}', [IntegrationController::class, 'show'])->name('integrations.system');

    Route::get('data-stack', [DataStackController::class, 'index'])->name('data-stack.index');
    Route::get('data-stack/export', [DataStackController::class, 'export'])->name('data-stack.export');

    Route::get('data-dictionary/entities', [CanonicalEntityController::class, 'index'])->name('data-dictionary.entities.index');
    Route::post('data-dictionary/entities', [CanonicalEntityController::class, 'store'])->name('data-dictionary.entities.store');
    Route::get('data-dictionary/entities/{canonicalEntity}', [CanonicalEntityController::class, 'show'])->name('data-dictionary.entities.show');
    Route::put('data-dictionary/entities/{canonicalEntity}', [CanonicalEntityController::class, 'update'])->name('data-dictionary.entities.update');
    Route::delete('data-dictionary/entities/{canonicalEntity}', [CanonicalEntityController::class, 'destroy'])->name('data-dictionary.entities.destroy');
    Route::post('data-dictionary/entities/{canonicalEntity}/attributes', [CanonicalEntityController::class, 'storeAttribute'])->name('data-dictionary.entities.attributes.store');
    Route::delete('data-dictionary/entities/{canonicalEntity}/attributes/{attribute}', [CanonicalEntityController::class, 'destroyAttribute'])->name('data-dictionary.entities.attributes.destroy');

    Route::get('platform-schemas', [PlatformSchemaController::class, 'index'])->name('platform-schemas.index');
    Route::post('platform-schemas', [PlatformSchemaController::class, 'store'])->name('platform-schemas.store');
    Route::get('platform-schemas/{platformSchema}', [PlatformSchemaController::class, 'show'])->name('platform-schemas.show');
    Route::put('platform-schemas/{platformSchema}', [PlatformSchemaController::class, 'update'])->name('platform-schemas.update');
    Route::delete('platform-schemas/{platformSchema}', [PlatformSchemaController::class, 'destroy'])->name('platform-schemas.destroy');
    Route::post('platform-schemas/{platformSchema}/fields', [PlatformSchemaController::class, 'storeField'])->name('platform-schemas.fields.store');
    Route::delete('platform-schemas/{platformSchema}/fields/{field}', [PlatformSchemaController::class, 'destroyField'])->name('platform-schemas.fields.destroy');
    Route::post('systems/{system}/import-schemas', [PlatformSchemaController::class, 'importFromSystem'])->name('platform-schemas.import');

    Route::get('field-mappings', [FieldMappingController::class, 'index'])->name('field-mappings.index');
    Route::post('field-mappings', [FieldMappingController::class, 'store'])->name('field-mappings.store');
    Route::delete('field-mappings/{fieldMapping}', [FieldMappingController::class, 'destroy'])->name('field-mappings.destroy');
});
