<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inviteables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('model_calendar_id')->nullable();
            $table->morphs('inviteable');
            $table->string('email')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('event_id')
                ->references('id')
                ->on('calendar_events')
                ->onDelete('cascade');
            $table->foreign('model_calendar_id')
                ->references('id')
                ->on('calendars')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invites');
    }
};
