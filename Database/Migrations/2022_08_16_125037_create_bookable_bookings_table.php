<?php

use Caiocesar173\Utils\Enum\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookableBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookable_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('bookable');
            $table->uuidMorphs('customer');

            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('canceled_at')->nullable();

            $table->string('timezone')->nullable();
            $table->decimal('price')->default('0.00');
            $table->integer('quantity')->unsigned();
            $table->decimal('total_paid')->default('0.00');
            $table->string('currency', 3);
            $table->json('formula')->nullable();
            $table->schemalessAttributes('options');
            $table->text('notes')->nullable();
            
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
        Schema::dropIfExists('bookable_bookings');
    }
}
