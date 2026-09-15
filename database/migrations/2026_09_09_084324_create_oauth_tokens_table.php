<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oauth_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->default('central-auth');
            $table->string('client_id');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('access_token_expires_at');
            $table->timestamp('refresh_token_expires_at')->nullable();
            $table->timestamps();
            $table->unique([
                'user_id', 'client_id',
            ]);
            $table->index('access_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_tokens');
    }
};
