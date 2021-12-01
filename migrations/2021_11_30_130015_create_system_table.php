<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateSystemTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 10)->default('')->comment('类型：site=站点信息');
            $table->string('genre', 10)->default('')->comment('字段类型：input/textarea/enable/url/email');
            $table->string('label', 10)->default('')->comment('名称');
            $table->string('key', 20)->default('')->comment('键');
            $table->text('val')->nullable()->comment('值');
            $table->unsignedTinyInteger('required')->default(0)->comment('必填：0=否；1=是');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('系统表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system');
    }
}
