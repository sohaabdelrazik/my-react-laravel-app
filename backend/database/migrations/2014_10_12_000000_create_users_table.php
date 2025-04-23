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
            $table->integer('age')->nullable();
            $table->decimal('rate', 5, 2)->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->string('mobile_number',15);
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
            $table->string('mobile_number',15);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charity_id')->constrained('charities')->onDelete('cascade');
            $table->string('charity_name');
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
        //pivot table to add state going to or interested
        Schema::create('event_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->enum('state', ['interested', 'going_to']);
            $table->timestamps();
        });//pivot table to add verification if user show up in real event into page previous events attended
        Schema::create('event_user_verified', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->enum('state', ['verified', 'unverified']);
            $table->timestamps();
        });

        // Create triggers using raw SQL
        DB::unprepared("
        CREATE TRIGGER before_insert_event_status BEFORE INSERT ON events
        FOR EACH ROW
        BEGIN
            IF NEW.due_date > NOW() THEN
                SET NEW.status = 'Future';
            ELSEIF DATE(NEW.due_date) = DATE(NOW()) THEN
                SET NEW.status = 'In_Progress'; 
            ELSE
                SET NEW.status = 'Previous';
            END IF;
        END;
    ");
    
    DB::unprepared("
        CREATE TRIGGER before_update_event_status BEFORE UPDATE ON events
        FOR EACH ROW
        BEGIN
            IF NEW.due_date > NOW() THEN
                SET NEW.status = 'Future';
            ELSEIF DATE(NEW.due_date) = DATE(NOW()) THEN
                SET NEW.status = 'In_Progress';   
            ELSE
                SET NEW.status = 'Previous';
            END IF;
        END;
    ");
    

    }

    public function down()
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('events');
        Schema::dropIfExists('charities');
        Schema::dropIfExists('users');
        Schema::dropIfExists('event_user');
        Schema::dropIfExists('event_user_verified');


    }
};
