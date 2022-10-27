<?php

use Caiocesar173\Utils\Enum\StatusEnum;
use Caiocesar173\Booking\Enum\DatesRangeEnum;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookable_fee', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuidMorphs('bookable');
            $table->enum('range', DatesRangeEnum::keys())->default(DatesRangeEnum::DATETIMES);
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->decimal('base_cost', 11, 2)->nullable()->default(0.00);
            $table->decimal('unit_cost', 11, 2)->nullable()->default(0.00);
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bookable_fee');
    }
};
