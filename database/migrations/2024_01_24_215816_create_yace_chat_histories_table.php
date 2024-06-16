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
        Schema::create('jace_chat_histories', function (Blueprint $table) {
            $table->id();
            $table->string('chat_history_uid')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('type', 15);
            $table->string('guest_id')->nullable();
            $table->text('message');
            $table->text('blocks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jace_chat_histories');
    }
};
