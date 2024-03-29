<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('location_images')) {
            return;
        }
        Schema::create('location_images', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('location_id');
            $table->string('name');
            $table->string('path');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_images');
    }
};
