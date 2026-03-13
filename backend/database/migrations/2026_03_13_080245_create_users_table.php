<?php

use App\Models\Branch;
use App\Models\Store;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Branch::class)->nullable();
            $table->foreignIdFor(Store::class)->nullable();

            // Identity
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('national_id', 20)->nullable()->unique();

            // Account Status
            $table->enum('account_status', [
                'active',
                'inactive',
                'suspended',
                'pending',
            ])->default('active');

            // Auth / Security
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamp('password_expires_at')->nullable();
            $table->string('password_reset_token', 100)->nullable();
            $table->timestamp('password_reset_token_expires_at')->nullable();

                                                                     // OTP (2FA step 2)
            $table->string('otp', 250)->nullable();                  // hashed OTP
            $table->timestamp('otp_expires_at')->nullable();         // 10 min TTL
            $table->unsignedTinyInteger('otp_attempts')->default(0); // max 3 attempts

                                                                     // Login token (2FA step 1 → step 2 bridge)
            $table->string('login_token', 150)->nullable();          // issued after credentials pass
            $table->timestamp('login_token_expires_at')->nullable(); // 10 min TTL

            // Session Tracking
            $table->unsignedInteger('today_logins_count')->default(0);
            $table->unsignedInteger('all_time_logins_count')->default(0);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 150)->nullable();
            $table->boolean('is_online')->default(false);
            $table->unsignedSmallInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'account_status']);
            $table->index(['store_id', 'account_status']);
            $table->index('account_status');
            $table->index('login_token'); // fast lookup on OTP verify step
            $table->index('password_expires_at');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 150)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
