<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAdminTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 20)->nullable()->index()->comment('用户名');
            $table->string('mobile', 20)->nullable()->index()->comment('手机号');
            $table->string('email', 60)->nullable()->index()->comment('邮箱');
            $table->string('nickname', 20)->default('')->comment('昵称');
            $table->string('avatar', 120)->default('')->comment('头像');
            $table->string('password', 64)->default('')->comment('密码');
            $table->string('signature', 64)->default('')->comment('签名');
            $table->unsignedTinyInteger('is_enable')->default(0)->comment('是否启用：0=否；1=是');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('管理员表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
}
