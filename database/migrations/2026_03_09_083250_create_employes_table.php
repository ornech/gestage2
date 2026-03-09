<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idEntreprise');
            $table->string('nom');
            $table->string('prenom');
            $table->string('email');
            $table->string('telephone');
            $table->string('service')->nullable();
            $table->string('fonction')->nullable();
            $table->unsignedBigInteger('created_userid')->nullable();
            $table->date('created_date')->nullable();
            $table->boolean('contact_valide')->default(false);
            $table->boolean('newsletter')->default(false);
            $table->boolean('jury')->default(false);
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employes');
    }
};
