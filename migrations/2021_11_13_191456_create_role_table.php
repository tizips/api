<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateRoleTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('slug', 60)->nullable()->comment('标识');
            $table->string('name', 20)->default('')->comment('名称');
            $table->string('summary')->default('')->comment('简介');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('角色表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role');
    }
}
