<?php namespace Waka\Agg\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateMonthsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_agg_months', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->boolean('is_reday')->default(false);
            $table->integer('ag_month')->unsigned();
            $table->integer('ag_year')->unsigned();
            $table->integer('data_source_id')->unsigned();
            $table->integer('nb_l')->nullable();
            $table->integer('nb')->nullable();
            $table->double('avg')->nullable();
            $table->double('amount')->nullable();
            $table->text('datas')->nullable();
            $table->date('start_at');
            $table->date('end_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_months');
        Schema::dropIfExists('waka_agg_montheable');
    }
}
