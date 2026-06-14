<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canonical_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug', 191)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('canonical_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canonical_entity_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 191);
            $table->string('data_type', 50)->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('constraints')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['canonical_entity_id', 'slug']);
        });

        Schema::create('platform_schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug', 191);
            $table->text('description')->nullable();
            $table->string('data_layer', 20)->default('bronze');
            $table->string('source_type', 30)->default('manual');
            $table->string('version', 50)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['system_id', 'slug']);
        });

        Schema::create('platform_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_schema_id')->constrained()->cascadeOnDelete();
            $table->string('native_name', 191);
            $table->string('native_path')->nullable();
            $table->string('data_type', 50)->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_primary_key')->default(false);
            $table->boolean('nullable')->default(true);
            $table->string('sample_value')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['platform_schema_id', 'native_name']);
        });

        Schema::create('field_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_field_id')->constrained()->cascadeOnDelete();
            $table->foreignId('canonical_attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('direction', 20)->default('bidirectional');
            $table->text('transform_rule')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['platform_field_id', 'canonical_attribute_id'], 'field_mapping_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_mappings');
        Schema::dropIfExists('platform_fields');
        Schema::dropIfExists('platform_schemas');
        Schema::dropIfExists('canonical_attributes');
        Schema::dropIfExists('canonical_entities');
    }
};
