<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('property_units', function (Blueprint $table) {
            // Drop the existing kitchen column
            $table->dropColumn('kitchen');
        });

        Schema::table('property_units', function (Blueprint $table) {
            // Add kitchen column as enum to match form select field
            $table->enum('kitchen', ['yes', 'no'])->nullable()->after('baths');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('property_units', function (Blueprint $table) {
            // Drop the enum kitchen column
            $table->dropColumn('kitchen');
        });

        Schema::table('property_units', function (Blueprint $table) {
            // Add back the original integer kitchen column
            $table->integer('kitchen')->default(0)->after('baths');
        });
    }
}; 