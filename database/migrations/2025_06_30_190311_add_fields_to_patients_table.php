<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->enum('type_document', [
                'OTRO', 'CONSUMIDOR FINAL', 'REGISTRO CIVIL', 'TI', 'CC', 'TE', 'Cédula de extranjería',
                'NIT', 'PASAPORTE', 'Documento de identidad', 'Sin identificación', 'Permiso especial', 'NUIP'
            ])->nullable();

            $table->enum('sex', ['male', 'female'])->nullable();
            $table->date('birthday')->nullable();
            $table->unsignedBigInteger('born_city_id')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable();

            $table->enum('type_user', [
                'Contributivo cotizante', 'Contributivo beneficiario', 'Contributivo adicional', 'Subsidiado', 'Sin régimen',
                'Especiales o de Excepción cotizante', 'Especiales o de Excepción beneficiario', 'Particular',
                'Tomador/Amparo ARL', 'Tomador/Amparo SOAT', 'Tomador/Amparo Planes voluntarios de salud'
            ])->nullable();

            $table->enum('disability', ['Si', 'NO'])->nullable();

            $table->foreign('born_city_id')->references('id')->on('cities')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'type_document', 'sex', 'birthday', 'born_city_id', 'address', 'phone',
                'email', 'type_user', 'disability'
            ]);
        });
    }

}
