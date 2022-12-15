<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('age')->default(0);
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('country_code')->nullable();
            $table->string('mobile')->nullable();
            $table->string('profile_image')->nullable()->default(null);
            $table->string('email')->nullable();
            $table->enum('type', ['admin', 'user'])->default('user');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->longText('reset_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
