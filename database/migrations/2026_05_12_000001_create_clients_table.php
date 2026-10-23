<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('person_name');
            $table->string('company');
            $table->string('mobile')->nullable();
            $table->string('category')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->string('sales_rep')->nullable();
            $table->string('email')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
