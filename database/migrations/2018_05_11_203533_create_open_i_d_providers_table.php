<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenIDProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('open_i_d_providers', function (Blueprint $table) {
            $table->increments('id');

            $table->uuid('tenant_id')->index();

            $table->integer('liftime_access_token');
            $table->integer('liftime_refresh_token');
            $table->integer('liftime_id_token');

            $table->string('response_types_supported');
            $table->string('acr_values_supported')->nullable();

            $table->string('profile_url_template', 255)->nullable();

            $table->string('init_url', 255)->nullable();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('open_i_d_providers');
    }
}
