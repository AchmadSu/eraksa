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
        Schema::disableForeignKeyConstraints();
        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('category_assets');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('placement_id')->references('id')->on('placements');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign('category_id');
            $table->dropForeign('user_id');
            $table->dropForeign('placement_id');
        });
    }
};
