<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talleres', function (Blueprint $table) {
            if (!Schema::hasColumn('talleres', 'texto_garantia')) {
                $table->text('texto_garantia')->nullable()->after('ciudad');
            }
            if (!Schema::hasColumn('talleres', 'prefijo_orden')) {
                $table->string('prefijo_orden', 10)->default('OT')->after('texto_garantia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('talleres', function (Blueprint $table) {
            $table->dropColumn(['texto_garantia', 'prefijo_orden']);
        });
    }
};
