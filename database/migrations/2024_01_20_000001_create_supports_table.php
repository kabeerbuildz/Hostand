<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supports', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'open', 'close', 'on_hold'])->default('pending');
            $table->text('attachment')->nullable();
            $table->unsignedBigInteger('created_id');
            $table->unsignedBigInteger('assign_user')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('created_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assign_user')->references('id')->on('users')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('supports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supports');
    }
} 