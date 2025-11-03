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
            $table->integer('double_beds')->default(0)->after('bedroom');
            $table->integer('single_beds')->default(0)->after('double_beds');
            $table->integer('sofa_beds')->default(0)->after('single_beds');
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
            $table->dropColumn([
                'double_beds',
                'single_beds',
                'sofa_beds'
            ]);
        });
    }
}; 