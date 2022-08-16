<?php

use Caiocesar173\Utils\Enum\StatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketableTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticketable_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuidMorphs('ticketable');
            $table->string('slug');
            $table->json('name');
            $table->json('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('price')->default('0.00');
            $table->string('currency', 3)->nullable();
            $table->integer('quantity')->nullable()->default(-1);
            $table->mediumInteger('sort_order')->unsigned()->default(0);

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
        Schema::dropIfExists('ticketable_tickets');
    }
}
