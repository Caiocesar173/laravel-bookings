<?php

use Caiocesar173\Booking\Enum\DatesRangeEnum;
use Caiocesar173\Utils\Enum\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookableAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookable_availabilities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('bookable');
            $table->enum('range', DatesRangeEnum::keys())->default(DatesRangeEnum::DATETIMES);
            $table->timestamp('from')->nullable();
            $table->timestamp('to')->nullable();
            $table->boolean('is_bookable')->default(true);
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
        Schema::dropIfExists('bookable_availabilities');
    }
}
