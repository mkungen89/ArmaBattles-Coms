<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // OAuth Clients (applications that can authenticate)
        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('name');
            $table->text('secret');
            $table->text('redirect_uris'); // JSON array
            $table->boolean('revoked')->default(false);
            $table->timestamps();
        });

        // OAuth Authorization Codes (temporary codes for token exchange)
        Schema::create('oauth_authorization_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('client_id', 100);
            $table->text('scopes')->nullable();
            $table->string('code', 100)->unique();
            $table->string('redirect_uri');
            $table->timestamp('expires_at');
            $table->boolean('revoked')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('oauth_clients')->onDelete('cascade');
        });

        // OAuth Access Tokens (long-lived tokens for API access)
        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('client_id', 100);
            $table->text('scopes')->nullable();
            $table->string('token', 100)->unique();
            $table->timestamp('expires_at');
            $table->boolean('revoked')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('oauth_clients')->onDelete('cascade');
        });

        // OAuth Refresh Tokens (used to get new access tokens)
        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100);
            $table->string('token', 100)->unique();
            $table->timestamp('expires_at');
            $table->boolean('revoked')->default(false);
            $table->timestamps();

            $table->foreign('access_token_id')->references('id')->on('oauth_access_tokens')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_authorization_codes');
        Schema::dropIfExists('oauth_clients');
    }
};
