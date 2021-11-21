<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreatePermissionTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permission', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('parent_i1')->nullable()->index()->comment('一级父级');
            $table->unsignedInteger('parent_i2')->nullable()->index()->comment('二级父级');
            $table->string('name', 20)->default('')->comment('名称');
            $table->string('slug', 60)->nullable()->comment('标识');
            $table->string('method', 10)->nullable()->index()->comment('接口请求方法');
            $table->string('path', 120)->nullable()->index()->comment('接口路径');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('权限表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission');
    }
}
