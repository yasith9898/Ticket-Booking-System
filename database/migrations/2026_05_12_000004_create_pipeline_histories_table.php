<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_card_id')->constrained()->cascadeOnDelete();
            $table->string('stage_name');
            $table->text('note')->nullable();
            $table->date('happened_on');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_histories');
    }
};
