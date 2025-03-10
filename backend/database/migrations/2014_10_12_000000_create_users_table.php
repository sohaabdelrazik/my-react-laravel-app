<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();         
            $table->timestamps();
        });

        Schema::create('charities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('specialty');
            $table->string('address')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charity_id')->constrained('charities')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High'])->nullable();
            $table->string('category')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('interested_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('going_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('previous_events_attended', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->timestamp('attended_at')->useCurrent();
        });

        // Create triggers using raw SQL
        DB::unprepared("
        CREATE TRIGGER before_insert_event_status BEFORE INSERT ON events
        FOR EACH ROW
        BEGIN
            IF NEW.due_date >= NOW() THEN
                SET NEW.status = 'Future';
            ELSE
                SET NEW.status = 'Previous';
            END IF;
        END;
    ");
    
    DB::unprepared("
        CREATE TRIGGER before_update_event_status BEFORE UPDATE ON events
        FOR EACH ROW
        BEGIN
            IF NEW.due_date >= NOW() THEN
                SET NEW.status = 'Future';
            ELSE
                SET NEW.status = 'Previous';
            END IF;
        END;
    ");

        DB::unprepared("
            CREATE TRIGGER update_previous_events AFTER UPDATE ON events
            FOR EACH ROW
            BEGIN
                IF NEW.status = 'Previous' THEN
                    INSERT INTO previous_events_attended (user_id, event_id, attended_at)
                    SELECT user_id, event_id, NOW()
                    FROM going_events
                    WHERE event_id = NEW.id;
                END IF;
            END;
        ");
    }

    public function down()
    {
        Schema::dropIfExists('previous_events_attended');
        Schema::dropIfExists('going_events');
        Schema::dropIfExists('interested_events');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('events');
        Schema::dropIfExists('charities');
        Schema::dropIfExists('users');
    }
};
