<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ticket_number')->unique();
            $table->string('subject');
            $table->string('client_name')->nullable();
            $table->string('company')->nullable();
            $table->string('mobile')->nullable();
            $table->string('client_email')->nullable()->index();
            $table->string('employee')->nullable();
            $table->string('sales_rep')->nullable();
            $table->string('status');
            $table->string('priority')->default('Normal');
            $table->date('opened_on');
            $table->date('due_on')->nullable();
            $table->text('public_comment')->nullable();
            $table->text('internal_note')->nullable();
            $table->unsignedTinyInteger('workflow_step')->default(1);
            $table->boolean('payment_ok')->default(true);
            $table->string('product')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
