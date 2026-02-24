<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {Schema::create('user', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('idTuteur')->nullable();
    $table->unsignedBigInteger('idClasse')->nullable();

    $table->string('nom');
    $table->string('prenom');
    $table->string('email')->unique();

    $table->date('date_entree')->nullable();
    $table->string('telephone')->nullable();

    $table->string('spe')->nullable();
    $table->string('classe')->nullable();
    $table->string('promo')->nullable();

    $table->string('login')->unique();
    $table->string('password');

    $table->string('password_reset')->nullable();

    $table->string('statut')->nullable();
    $table->boolean('inactif')->default(false);

    $table->dateTime('dateFirstConn')->nullable();

    $table->boolean('isDeleted')->default(false);

    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
