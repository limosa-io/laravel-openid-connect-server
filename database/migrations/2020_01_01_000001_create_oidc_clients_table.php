<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOidcClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oidc_clients', function (Blueprint $table) {

            $table->uuid('client_id')->primary();

            // TODO: not used??
            $table->uuid('user_id')->index()->nullable();
            $table->string('name');

            //client_secret
            $table->string('secret', 100);

            $table->text('redirect_uris')->nullable();

            $table->text('post_logout_redirect_uris')->nullable();

            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();

            $table->string('response_types')->default('["token","code","id_token"]')->nullable();

            $table->string('grant_types')->default('["authorization_code"]');

            $table->string('code_challenge_methods_supported')->nullable();

            $table->string('application_type')->default('web');
            $table->string('public')->default('confidential');
            $table->text('contacts')->nullable();
            $table->string('logo_uri')->nullable();
            $table->string('client_uri')->nullable();
            $table->string('policy_uri')->nullable();
            $table->string('tos_uri')->nullable();

            $table->string('token_endpoint_auth_method')->default('client_secret_post');

            $table->string('jwks_uri')->nullable();
            $table->text('jwks')->nullable();

            $table->string('default_max_age')->nullable();
            $table->text('default_acr_values')->nullable();

            $table->string('default_prompt')->nullable();

            $table->boolean('default_prompt_allow_override')->default(true);
            $table->boolean('default_acr_values_allow_override')->default(true);

            $table->string('require_auth_time')->nullable();

            $table->string('initiate_login_uri')->nullable();

            $table->boolean('trusted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('oidc_clients');
    }
}
