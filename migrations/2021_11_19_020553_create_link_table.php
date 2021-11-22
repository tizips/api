<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateLinkTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('link', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 20)->default('')->comment('名称');
            $table->string('uri', 120)->default('')->comment('链接');
            $table->string('admin', 120)->default('')->comment('站长');
            $table->string('email', 120)->default('')->comment('邮箱');
            $table->string('logo', 120)->default('')->comment('Logo');
            $table->string('summary', 120)->default('')->comment('简介');
            $table->unsignedTinyInteger('no')->default(50)->comment('序号');
            $table->unsignedTinyInteger('position')->default(0)->comment('位置：1=页底；2=其他');
            $table->unsignedTinyInteger('is_enable')->default(0)->comment('启用：0=否；1=是');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('友链表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('link');
    }
}
