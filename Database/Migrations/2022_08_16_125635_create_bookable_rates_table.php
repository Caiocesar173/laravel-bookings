<?php

use Caiocesar173\Utils\Enum\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookableRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookable_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuidMorphs('bookable');
            $table->string('range');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('base_cost')->nullable();
            $table->string('unit_cost')->nullable();
            $table->smallInteger('priority')->unsigned()->nullable();

            $table->enum('status', StatusEnum::keys())->default(StatusEnum::ACTIVE);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookable_rates');
    }
}
