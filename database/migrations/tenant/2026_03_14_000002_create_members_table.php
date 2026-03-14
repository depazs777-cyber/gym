<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_party_id')->constrained('third_parties')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->string('membership_type')->nullable();
            $table->date('birth_date')->nullable();

            $table->string('gender')->nullable();

            $table->string('emergency_contact')->nullable();

            $table->boolean('terms_accepted')->default(false);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('qr_token')->nullable()->unique();
            $table->timestamp('qr_token_expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
