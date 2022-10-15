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
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('otp');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->enum('status', ['1', '0'])->nullable()->default('0');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('verification_codes');
        // $table->dropForeign('verification_codes_user_id_foreign');
    }
};
