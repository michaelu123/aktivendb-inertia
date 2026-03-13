<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sbmembers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("member_id");
            $table->foreign('member_id')->references('id')->on('members')->onDelete("cascade");
            $table->string('first_name');
            $table->string('last_name');
            $table->boolean('speicherungok');
            $table->boolean('aktiv');
            $table->string('email_adfc')->nullable();
            $table->string('email_private')->nullable();
            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('address')->nullable();
            $table->string('adfc_id')->nullable();
            $table->string('gender')->nullable();
            $table->text('interests')->nullable();
            $table->string('birthday')->nullable();
            $table->string('admin_comments')->nullable();
            $table->datetime('eingetragen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbmembers');
    }
};
