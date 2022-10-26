<?php

use Caiocesar173\Utils\Enum\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookableTicketTable extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookable_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug');
			$table->foreignUuid('booking')->references('id')->on('bookable_bookings')->onDelete('cascade');
            $table->uuidMorphs('responsable');
            $table->decimal('price')->default(0.00);
			$table->foreignUuid('currencies')->references('id')->on('currencies')->onDelete('cascade');
            $table->integer('quantity')->nullable()->default(1);
            $table->bigInteger('sort_order')->unsigned();
            $table->boolean('is_paied')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_confirmed')->default(false);
            $table->boolean('is_attended')->default(false);
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bookable_tickets');
    }
}
