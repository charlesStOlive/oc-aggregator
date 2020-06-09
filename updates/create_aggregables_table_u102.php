<?php namespace Waka\Agg\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateAggregablesTableU102 extends Migration
{
    public function up()
    {
        Schema::table('waka_agg_aggregables', function (Blueprint $table) {
            $table->index(['aggregable_id', 'aggregable_type'], 'aggregable');
            $table->index(['periodeable_id', 'periodeable_type'], 'periodeable');
        });
    }

    public function down()
    {
        Schema::table('waka_agg_aggregables', function (Blueprint $table) {
            $table->dropIndex('aggregable');
            $table->dropIndex('periodeable');
        });
    }
}
