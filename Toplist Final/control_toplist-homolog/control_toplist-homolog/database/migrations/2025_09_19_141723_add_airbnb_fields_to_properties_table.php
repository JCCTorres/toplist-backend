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
        Schema::table('properties', function (Blueprint $table) {
            // Campos específicos do Airbnb
            $table->bigInteger('airbnb_id')->nullable()->after('property_id')->comment('ID original do Airbnb');
            $table->text('airbnb_url')->nullable()->after('airbnb_id')->comment('URL da propriedade no Airbnb');
            $table->string('house_number', 50)->nullable()->after('title')->comment('Número da casa');
            $table->string('owner', 255)->nullable()->after('house_number')->comment('Proprietário da casa');
            $table->text('observations')->nullable()->after('owner')->comment('Observações sobre a propriedade');
            $table->string('address', 500)->nullable()->after('observations')->comment('Endereço da propriedade');
            
            // Campos de controle de origem
            $table->enum('source', ['airbnb', 'bookerville', 'manual'])->default('manual')->after('category')->comment('Origem dos dados');
            $table->json('original_data')->nullable()->after('details')->comment('Dados originais completos do JSON');
            
            // Índices para performance
            $table->index('airbnb_id', 'idx_properties_airbnb_id');
            $table->index('source', 'idx_properties_source');
            $table->index('house_number', 'idx_properties_house_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Remover índices primeiro
            $table->dropIndex('idx_properties_airbnb_id');
            $table->dropIndex('idx_properties_source');
            $table->dropIndex('idx_properties_house_number');
            
            // Remover colunas
            $table->dropColumn([
                'airbnb_id',
                'airbnb_url',
                'house_number',
                'owner',
                'observations',
                'address',
                'source',
                'original_data'
            ]);
        });
    }
};
