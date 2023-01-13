<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //DB::statement("ALTER TABLE users DROP COLUMN code");
        //DB::statement("ALTER TABLE users DROP COLUMN code_type");
        DB::statement("ALTER TABLE users ADD COLUMN code VARCHAR(255)");
        DB::statement("ALTER TABLE users ADD COLUMN code_type ENUM('0', '1') DEFAULT ('0')");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users DROP COLUMN code");
            DB::statement("ALTER TABLE users DROP COLUMN code_type");
        });
    }
};
