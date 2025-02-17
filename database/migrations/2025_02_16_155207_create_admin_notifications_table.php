<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('untitled');
            $table->foreignId('from_admin_id')->constrained('users');
            $table->foreignId('to_user_id')->constrained('users');
            $table->text('message');
            $table->string('type')->default('info'); // info, warning, success, error
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_notifications');
    }
};