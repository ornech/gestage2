<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->unsignedInteger('legacy_id')->nullable()->unique()->after('id')
                  ->comment('ID de la réalisation dans btssio17_legacy.journaux — null si créée dans gestage2');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });
    }
};
