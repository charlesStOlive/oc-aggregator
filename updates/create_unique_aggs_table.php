<?php namespace Waka\Agg\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateUniqueAggsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_agg_unique_aggs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('year_nb')->default(0);
            $table->integer('year_ms')->default(0);
            $table->text('year_datas')->nullable();
            $table->integer('year_nb_m')->default(0);
            $table->integer('year_ms_m')->default(0);
            $table->text('year_datas_m')->nullable();
            $table->integer('month_nb')->default(0);
            $table->integer('month_ms')->default(0);
            $table->text('month_datas')->nullable();
            $table->integer('month_nb_m')->default(0);
            $table->integer('month_ms_m')->default(0);
            $table->text('month_datas_m')->nullable();
            $table->integer('week_nb')->default(0);
            $table->integer('week_ms')->default(0);
            $table->text('week_datas')->nullable();
            $table->integer('week_nb_m')->default(0);
            $table->integer('week_ms_m')->default(0);
            $table->text('week_datas_m')->nullable();
            $table->integer('uniqueable_id')->unsigned();
            $table->string('uniqueable_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_unique_aggs');
    }
}
