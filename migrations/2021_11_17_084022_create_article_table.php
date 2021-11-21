<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateArticleTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('article', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('category_id')->default(0)->index()->comment('栏目ID');
            $table->string('name', 120)->default('')->comment('名称');
            $table->string('picture', 255)->default('')->comment('图片');
            $table->string('title', 255)->default('')->comment('SEO 标题');
            $table->string('keyword', 255)->default('')->comment('SEO 关键词');
            $table->string('description', 255)->default('')->comment('SEO 描述');
            $table->unsignedInteger('admin_id')->default(0)->comment('作者');
            $table->string('source_name', 20)->default('')->comment('转载标题');
            $table->string('source_uri', 120)->default('')->comment('转载链接');
            $table->text('content')->comment('内容');
            $table->unsignedTinyInteger('is_comment')->default(0)->comment('评论：0=关闭；1=开启');
            $table->unsignedTinyInteger('is_enable')->default(0)->comment('启用：0=否；1=是');
            $table->timestamps();
            $table->softDeletes();
            $table->comment('文章表');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article');
    }
}
