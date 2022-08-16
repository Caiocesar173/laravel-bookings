<?php

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
            $table->string('range');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->boolean('is_bookable')->default(false);
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
        Schema::dropIfExists('bookable_availabilities');
    }
}
