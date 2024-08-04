<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->ulid();
            $table->foreignId('calendar_id')->constrained('calendars')->cascadeOnDelete();
            $table->dateTimeTz('start');
            $table->dateTimeTz('end')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('repeat')->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->json('extended_props')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
};
