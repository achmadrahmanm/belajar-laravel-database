<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->string('id', 100)->nullable(false)->primary();
            $table->string('name', 255)->nullable(false);
            $table->string('description', 500)->nullable(true);
            $table->timestamp('created_at')->nullable(false)->useCurrent();
            // $table->timestamps('updated_at')->nullable(false)->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
