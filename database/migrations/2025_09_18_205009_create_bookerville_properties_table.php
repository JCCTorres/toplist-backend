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
        Schema::create('bookerville_properties', function (Blueprint $table) {
            $table->id();
            
            // Identificadores
            $table->string('property_id')->unique()->comment('ID da propriedade no Bookerville');
            $table->string('account_id')->nullable()->comment('ID da conta no Bookerville');
            
            // Informações básicas
            $table->string('name')->nullable()->comment('Nome da propriedade');
            $table->text('address')->nullable()->comment('Endereço completo');
            $table->string('city')->nullable()->comment('Cidade');
            $table->string('state')->nullable()->comment('Estado');
            $table->string('zip_code')->nullable()->comment('CEP');
            $table->string('country')->nullable()->comment('País');
            $table->string('property_type')->nullable()->comment('Tipo da propriedade');
            
            // Características da propriedade
            $table->integer('bedrooms')->default(0)->comment('Número de quartos');
            $table->decimal('bathrooms', 3, 1)->default(0)->comment('Número de banheiros');
            $table->integer('max_guests')->default(0)->comment('Máximo de hóspedes');
            
            // Descrições
            $table->longText('description')->nullable()->comment('Descrição da propriedade');
            $table->json('amenities')->nullable()->comment('Comodidades disponíveis');
            $table->json('images')->nullable()->comment('URLs das imagens');
            
            // Informações de reserva
            $table->json('booking_info')->nullable()->comment('Informações de preços e reserva');
            $table->json('availability')->nullable()->comment('Disponibilidade e datas bloqueadas');
            
            // Links externos
            $table->json('external_links')->nullable()->comment('Links para Airbnb, VRBO, etc');
            
            // Informações do gerente
            $table->json('manager')->nullable()->comment('Dados do gerente da propriedade');
            
            // Status e controle
            $table->boolean('off_line')->default(false)->comment('Se a propriedade está offline');
            $table->string('details_url')->nullable()->comment('URL da API de detalhes');
            
            // Dados brutos para debug/auditoria
            $table->longText('raw_summary_data')->nullable()->comment('Dados brutos do summary XML');
            $table->longText('raw_details_data')->nullable()->comment('Dados brutos dos detalhes XML');
            
            // Cache e sincronização
            $table->timestamp('last_summary_sync')->nullable()->comment('Última sincronização do summary');
            $table->timestamp('last_details_sync')->nullable()->comment('Última sincronização dos detalhes');
            
            $table->timestamps();
            
            // Índices
            $table->index('property_id');
            $table->index('account_id');
            $table->index('city');
            $table->index('state');
            $table->index('property_type');
            $table->index('off_line');
            $table->index('last_summary_sync');
            $table->index('last_details_sync');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookerville_properties');
    }
};
