<?php

use Caiocesar173\Utils\Enum\StatusEnum;

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
        Schema::create('bookable_bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('bookable');
            $table->uuidMorphs('customer');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->string('timezone')->nullable();
            $table->decimal('price')->nullable()->default(0.00);
            $table->integer('quantity')->unsigned()->default(1);
            $table->decimal('total_paid', 11, 2)->default(0.00);

			$table->foreignUuid('currencies')->references('id')->on('currencies')->onDelete('cascade');

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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('bookable_bookings');
    }
};
