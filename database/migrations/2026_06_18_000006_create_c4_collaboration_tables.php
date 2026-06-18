<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c4_model_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('commit_message');
            $table->json('snapshot');
            $table->string('branch')->default('main');
            $table->unsignedInteger('version_number');
            $table->timestamps();

            $table->unique(['system_id', 'branch', 'version_number']);
        });

        Schema::create('c4_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('commentable');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->uuid('parent_id')->nullable();
            $table->text('body');
            $table->boolean('resolved')->default(false);
            $table->json('mentions')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('c4_comments')->cascadeOnDelete();
        });

        Schema::create('architectural_decision_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('system_id')->nullable()->constrained('systems')->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('proposed');
            $table->text('context')->nullable();
            $table->text('decision')->nullable();
            $table->text('consequences')->nullable();
            $table->date('decided_at')->nullable();
            $table->json('reviewers')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('adr_c4_element', function (Blueprint $table) {
            $table->uuid('adr_id');
            $table->uuid('element_id');
            $table->string('element_type');
            $table->timestamps();

            $table->primary(['adr_id', 'element_id', 'element_type']);
            $table->foreign('adr_id')->references('id')->on('architectural_decision_records')->cascadeOnDelete();
        });

        Schema::create('c4_compliance_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('taggable');
            $table->string('tag');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['taggable_type', 'taggable_id', 'tag']);
        });

        Schema::create('c4_share_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('password')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('level')->default('context');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c4_share_links');
        Schema::dropIfExists('c4_compliance_tags');
        Schema::dropIfExists('adr_c4_element');
        Schema::dropIfExists('architectural_decision_records');
        Schema::dropIfExists('c4_comments');
        Schema::dropIfExists('c4_model_versions');
    }
};
