<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateCategoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('parent_id')->default(0)->index()->comment('父级ID');
            $table->string('name', 20)->default('')->comment('名称');
            $table->string('picture', 255)->default('')->comment('图片');
            $table->string('title', 255)->default('')->comment('SEO 标题');
            $table->string('keyword', 255)->default('')->comment('SEO 关键词');
            $table->string('description', 255)->default('')->comment('SEO 描述');
            $table->string('uri', 60)->default('')->comment('链接地址');
            $table->unsignedTinyInteger('no')->default(50)->comment('序号');
            $table->unsignedTinyInteger('is_page')->default(0)->index()->comment('是否单页面');
            $table->unsignedTinyInteger('is_comment')->default(0)->comment('评论：0=否；1=是');
            $table->unsignedTinyInteger('is_enable')->default(0)->comment('启用：0=否；1=是');
            $table->text('page')->nullable()->comment('页面内容');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('栏目表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category');
    }
}
