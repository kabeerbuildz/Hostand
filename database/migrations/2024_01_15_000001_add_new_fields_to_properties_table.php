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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('piano')->nullable()->after('address');
            $table->string('staircase')->nullable()->after('piano');
            $table->string('access_other')->nullable()->after('staircase');
            $table->string('sign_detail')->nullable()->after('access_other');
            $table->enum('opening_type', ['key', 'code'])->nullable()->after('sign_detail');
            $table->string('street_code')->nullable()->after('opening_type');
            $table->string('door_code')->nullable()->after('street_code');
            $table->string('key_description')->nullable()->after('door_code');
            $table->enum('sofa_bed', ['yes', 'no'])->nullable()->after('key_description');
            $table->enum('bnb_unit_type', ['double', 'triple', 'single'])->nullable()->after('sofa_bed');
            $table->integer('bnb_unit_count')->nullable()->after('bnb_unit_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'piano',
                'staircase',
                'access_other',
                'sign_detail',
                'opening_type',
                'street_code',
                'door_code',
                'key_description',
                'sofa_bed',
                'bnb_unit_type',
                'bnb_unit_count'
            ]);
        });
    }
}; 