<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pokemons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('name', 20);
            $table->string('type', 15);
            $table->string('ability', 15);
            $table->string('image', 120);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pokemons');
    }
};