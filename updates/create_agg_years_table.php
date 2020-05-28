<?php namespace Waka\Agg\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateAggYearsTable extends Migration
{
    public function up()
    {
        Schema::create('waka_agg_agg_years', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('nb')->default(0);
            $table->integer('amount')->default(0);
            $table->text('datas')->nullable();
            $table->integer('nb_m')->default(0);
            $table->integer('amount_m')->default(0);
            $table->text('datas_m')->nullable();
            $table->integer('uniqueable_id')->unsigned();
            $table->string('uniqueable_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waka_agg_agg_years');
    }
}
