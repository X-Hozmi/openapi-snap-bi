<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string> */
    private $tableNames = [
        'logger_access_openapi',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tableNames as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('method');
                $table->text('url');
                $table->ipAddress();
                $table->json('request_headers');
                $table->json('response_headers');
                $table->json('request_contents')->nullable();
                $table->json('response_contents')->nullable();
                $table->unsignedInteger('response_code');
                $table->float('processing_time')->comment('in milliseconds');
                $table->timestamps();

                $table->index('method');
                $table->index('ip_address');
                $table->index('response_code');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tableNames as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};
