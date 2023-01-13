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
        Schema::dropIfExists('loans');
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('loaner_id')->nullable();
            $table->foreign('loaner_id')->references('id')->on('users');
            $table->unsignedBigInteger('lender_id')->nullable();
            $table->foreign('lender_id')->references('id')->on('users');
            $table->unsignedBigInteger('return_id')->nullable();
            $table->enum('status', ['0', '1', '2'])
            ->nullable()
            ->default('0');
            $table->date('date');
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
};
