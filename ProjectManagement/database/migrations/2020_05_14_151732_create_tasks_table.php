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
            $table->tinyInteger("priority")->default(config("prioritize.not_important_not_urgent"));
            $table->timestamp("started_at")->nullable();
            $table->timestamp("end_at")->nullable();
            $table->tinyInteger("status")->default(0);
            $table->integer("project_id");
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
