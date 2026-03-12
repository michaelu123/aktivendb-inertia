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
            $table->string('email_adfc')->nullable();
            $table->string('email_private')->nullable();
            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('address')->nullable();
            $table->string('adfc_id')->nullable();
            $table->text('admin_comments')->nullable();
            $table->date('latest_first_aid_training')->nullable();
            $table->string('gender')->nullable();
            $table->text('interests')->nullable();
            $table->date('latest_contact')->nullable();
            $table->boolean('active')->default(true);
            $table->string('birthday')->nullable();
            $table->string('status', 4000)->nullable();
            $table->boolean('responded_to_questionaire')->nullable();
            $table->datetime('responded_to_questionaire_at')->nullable();
            $table->boolean('dsgvo_signature')->nullable();
            $table->boolean('police_certificate')->nullable();
            $table->date('polcert_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
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
