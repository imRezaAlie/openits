<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('systems', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('system_type')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedBigInteger('parent_system_id')->nullable();
            $table->timestamps();
        });

        Schema::table('systems', function (Blueprint $table) {
            $table->foreign('parent_system_id')->references('id')->on('systems')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('systems');
    }
};
