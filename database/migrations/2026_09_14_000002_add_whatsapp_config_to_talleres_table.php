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
        Schema::table('talleres', function (Blueprint $table) {
            $table->boolean('whatsapp_auto_notify_enabled')->default(false)->after('texto_garantia');
            $table->string('whatsapp_api_provider', 50)->default('webhook_personalizado')->after('whatsapp_auto_notify_enabled');
            $table->text('whatsapp_api_token')->nullable()->after('whatsapp_api_provider');
            $table->string('whatsapp_phone_number_id', 100)->nullable()->after('whatsapp_api_token');
            $table->string('whatsapp_webhook_url', 255)->nullable()->after('whatsapp_phone_number_id');
            $table->text('whatsapp_template_creada')->nullable()->after('whatsapp_webhook_url');
            $table->text('whatsapp_template_en_proceso')->nullable()->after('whatsapp_template_creada');
            $table->text('whatsapp_template_finalizada')->nullable()->after('whatsapp_template_en_proceso');
            $table->text('whatsapp_template_entregada')->nullable()->after('whatsapp_template_finalizada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('talleres', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_auto_notify_enabled',
                'whatsapp_api_provider',
                'whatsapp_api_token',
                'whatsapp_phone_number_id',
                'whatsapp_webhook_url',
                'whatsapp_template_creada',
                'whatsapp_template_en_proceso',
                'whatsapp_template_finalizada',
                'whatsapp_template_entregada',
            ]);
        });
    }
};
