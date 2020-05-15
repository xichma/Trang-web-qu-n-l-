<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string("content");
            $table->string("slug")->unique();
            $table->integer("sprint_id");
            $table->integer("parent_id");
            $table->tinyInteger("priority");
            $table->integer("created_by");
            $table->timestamp("started_at")->nullable();
            $table->timestamp("end_at")->nullable();
            $table->float("progress")->default(0);
            $table->tinyInteger("status")->default(0);
            $table->integer("check_by");
            $table->tinyInteger("check_result");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
