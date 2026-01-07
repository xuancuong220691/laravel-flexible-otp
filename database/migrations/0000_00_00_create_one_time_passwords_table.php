<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::connection(config('otp.connection'))->create('one_time_passwords', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->index();
            $table->string('token');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection(config('otp.connection'))->dropIfExists('one_time_passwords');
    }
};