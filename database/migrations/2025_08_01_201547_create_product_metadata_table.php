<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductMetadataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_metadata', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('name');
            $table->string('active_ingredient_code');
            $table->string('pharmaceutical_form_code');
            $table->string('presentation_code');
            $table->string('category_code');
            $table->integer('product_id')->nullable(true);

            // Foreign key usando el ID del producto
            //$table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Claves foráneas por código (sin restricciones físicas, solo informativo)
            // Puedes crear índices si lo deseas
            $table->index('category_code');
            $table->index('presentation_code');
            $table->index('active_ingredient_code');
            $table->index('pharmaceutical_form_code');

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
        Schema::dropIfExists('product_metadata');
    }
}
