<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // your custom columns may go here

            $table->foreignId("plan_id")->nullable()->constrained("plans")->onDelete("set null");

            $table->string("business_name")->nullable();

            $table->string("nit")->nullable();

            $table->string("address")->nullable();

            $table->string("phone")->nullable();

            $table->string("email")->nullable();

            $table->string("logo_path")->nullable();

            $table->date("subscription_start")->nullable();

            $table->date("subscription_end")->nullable();

            $table->boolean("is_active")->default(true);
            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
