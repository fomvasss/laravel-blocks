<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->index()->nullable(); // unique()
            $table->string('type')->index();
            $table->string('status');
            $table->string('name')->nullable();
            $table->json('content')->nullable();
            $table->json('ids')->nullable();
            $table->json('options')->nullable();
            $table->json('locales')->nullable();
            $table->integer('weight')->default(0);
            $table->timestamps();

        });

        Schema::create('blockable', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('block_id');
            $table->foreign('block_id')->references('id')->on('blocks')->constrained()->onDelete('cascade');
            $table->uuidMorphs('model');
            $table->integer('weight')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('blockable');
        Schema::dropIfExists('blocks');
    }
};
