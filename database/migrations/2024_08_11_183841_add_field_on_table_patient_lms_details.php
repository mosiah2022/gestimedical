<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldOnTablePatientLmsDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_lm_details', function(Blueprint $table) {
            $table->decimal('price_detail', 10,2)->after('prescription');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_lm_details', function(Blueprint $table) {
            $table->dropColumn('price_detail');
        });
    }
}
