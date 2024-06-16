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
        Schema::create('jace_oauth_providers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('provider_name', 100);
            $table->string('provider_user_id', 255);
            $table->string('email', 255);
            $table->string('username', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jace_oauth_providers');
    }
};
