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
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('loan_id')->nullable();
            $table->foreign('loan_id')->references('id')->on('loans');
            $table->unsignedBigInteger('loaner_id')->nullable();
            $table->foreign('loaner_id')->references('id')->on('users');
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->foreign('recipient_id')->references('id')->on('users');
            $table->date('date');
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
        Schema::dropIfExists('returns');
    }
};
