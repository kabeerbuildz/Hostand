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
        Schema::table('supports', function (Blueprint $table) {
            $table->unsignedBigInteger('request_id')->nullable()->after('id');

            // If request_id relates to a table (e.g., maintenance_requests), add the foreign key
            $table->foreign('request_id')
                  ->references('id')
                  ->on('maintenance_requests')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support', function (Blueprint $table) {
            $table->dropForeign(['request_id']);
            $table->dropColumn('request_id');
        });
    }
};
