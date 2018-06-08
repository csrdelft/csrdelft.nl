<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EetplanMigratie extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eetplan', function (Blueprint $table) {
            $table->date('avond')->nullable()->change();
        });

        // Date mag niet 0000-00-00 zijn in strict mode. Eloquent draait standaard in strict mode.
        DB::table('eetplan')->where('avond', '=', '0000-00-00')->update(['avond'=> null]);

        Schema::table('eetplan', function (Blueprint $table) {
            $table->dropPrimary(['uid', 'woonoord_id']);
        });

        Schema::table('eetplan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->unique(['uid', 'woonoord_id']);
            $table->foreign('uid')->references('uid')->on('profielen');
            $table->foreign('woonoord_id')->references('id')->on('woonoorden');
        });

        Schema::table('eetplan_bekenden', function (Blueprint $table) {
            $table->dropPrimary(['uid1', 'uid2']);
        });

        Schema::table('eetplan_bekenden', function (Blueprint $table) {
            $table->integer('id', true);
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->unique(['uid1', 'uid2']);
            $table->foreign('uid1')->references('uid')->on('profielen');
            $table->foreign('uid2')->references('uid')->on('profielen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eetplan', function (Blueprint $table) {
            $table->dropForeign(['uid']);
            $table->dropForeign(['woonoord_id']);
            $table->dropUnique(['uid', 'woonoord_id']);
        });

        Schema::table('eetplan', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('eetplan', function (Blueprint $table) {
            $table->primary(['uid', 'woonoord_id']);
        });

        Schema::table('eetplan_bekenden', function (Blueprint $table) {
            $table->dropColumn('id');
        });

        Schema::table('eetplan_bekenden', function (Blueprint $table) {
            $table->dropForeign(['uid1']);
            $table->dropForeign(['uid2']);
        });

        Schema::table('eetplan_bekenden', function (Blueprint $table) {
            $table->dropUnique(['uid1', 'uid2']);
            $table->primary(['uid1', 'uid2']);
        });
    }
}
