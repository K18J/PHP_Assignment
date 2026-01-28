<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('serial_number', 100)->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('value', 12, 2)->default(0);
            $table->string('status', 50)->default('active'); // active, inactive, maintenance, retired
            $table->text('description')->nullable();
            $table->date('purchase_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
};
