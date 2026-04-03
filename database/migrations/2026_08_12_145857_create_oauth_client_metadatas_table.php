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
        Schema::create('oauth_client_metadatas', function (Blueprint $table) {
            $table->uuid('client_id')->primary();
            $table->string('client_secret')->comment('Unhashed version of secret from oauth_clients');
            $table->unsignedBigInteger('channel_id')->comment('SNAP API Integration')->nullable();
            $table->unsignedBigInteger('partner_id')->comment('SNAP API Integration');
            $table->string('source_code', 100)->nullable()->comment('Mostly, this column filled from bank code');
            $table->string('kdpp', 100)->nullable();
            $table->ipAddress()->nullable();
            $table->string('public_key_file');
            $table->string('private_key_file')->nullable();
            $table->string('scope')->default('')->comment('Separate with space if there\'s more than one scopes to be defined');
            $table->unsignedInteger('rupiah_admin')->default(0)->comment('Additional information of transaction fee');
            $table->timestamps();

            $table->unique('client_secret');
            $table->unique('channel_id');
            $table->unique('partner_id');
            $table->unique('source_code');
            $table->unique('kdpp');
            $table->unique('public_key_file');

            $table->index('scope');

            $table->foreign('client_id')->references('id')->on('oauth_clients')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_client_metadatas');
    }
};
