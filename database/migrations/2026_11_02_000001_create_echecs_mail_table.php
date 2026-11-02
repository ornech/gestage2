<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('echecs_mail', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('mail');                // libellé du mail (classe Mailable)
            $table->text('erreur');                // message exact du serveur / du transport
            $table->timestamp('resolu_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('echecs_mail');
    }
};
