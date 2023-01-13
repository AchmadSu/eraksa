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
        Schema::dropIfExists('assets');
        // Schema::enableForeignKeyConstraints();
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->date('date');
            $table->enum('condition', ['0', ['1']])->default('0');
            $table->enum('status', ['0', '1'])->default('0');
            $table->foreignId('placement_id')->nullable();
            $table->timestamps();

        });
        // Schema::table('assets', function(Blueprint $table){
        //     // $table->dropForeign('assets_category_id_foreign');
        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        //     $table->foreign('category_id')->references('id')->on('category_assets')->onDelete('cascade');
        //     $table->foreign('placement_id')->references('id')->on('placement')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('assets');
    }
};
