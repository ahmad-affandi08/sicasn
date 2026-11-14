<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturans', function (Blueprint $table) {
            $table->id();
            $table->string('grup', 50);
            $table->string('kunci', 100);
            $table->text('nilai')->nullable();
            $table->boolean('terenkripsi')->default(false);
            $table->timestamps();
            $table->unique(['grup', 'kunci']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturans');
    }
};
