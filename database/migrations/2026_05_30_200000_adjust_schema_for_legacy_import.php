<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adjustments required to accommodate legacy data during import:
 *   - journal_entries.date_debut_semaine → nullable (old DB has no week-start date)
 *   - employes.email / telephone          → nullable (many old contacts had no email/phone)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->date('date_debut_semaine')->nullable()->change();
        });

        Schema::table('employes', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('telephone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->date('date_debut_semaine')->nullable(false)->change();
        });

        Schema::table('employes', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('telephone')->nullable(false)->change();
        });
    }
};
