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
            // Campos básicos do Bookerville
            $table->string('zip_code')->nullable()->after('address');
            $table->string('country', 2)->nullable()->after('zip_code');
            $table->string('property_type')->nullable()->after('country');
            $table->integer('max_guests')->nullable()->after('property_type');
            $table->longText('description')->nullable()->after('max_guests');
            $table->text('main_image')->nullable()->after('description');
            
            // Campos JSON para estruturas complexas
            $table->json('amenities')->nullable()->after('main_image');
            $table->json('photos')->nullable()->after('amenities');
            $table->json('summary_data')->nullable()->after('photos');
            
            // Metadados do Bookerville
            $table->string('bookerville_id')->nullable()->after('summary_data');
            $table->string('bkv_account_id')->nullable()->after('bookerville_id');
            $table->string('manager_first_name')->nullable()->after('bkv_account_id');
            $table->string('manager_last_name')->nullable()->after('manager_first_name');
            $table->string('manager_phone')->nullable()->after('manager_last_name');
            $table->string('business_name')->nullable()->after('manager_phone');
            $table->string('email_address_account')->nullable()->after('business_name');
            $table->boolean('off_line')->default(false)->after('email_address_account');
            $table->string('property_details_api_url')->nullable()->after('off_line');
            
            // Timestamps específicos do Bookerville
            $table->timestamp('bookerville_last_update')->nullable()->after('property_details_api_url');
            $table->timestamp('bookerville_created_at')->nullable()->after('bookerville_last_update');
            $table->timestamp('bookerville_updated_at')->nullable()->after('bookerville_created_at');
            
            // Índices para performance
            $table->index('bookerville_id');
            $table->index('bkv_account_id');
            $table->index('property_type');
            $table->index('max_guests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['bookerville_id']);
            $table->dropIndex(['bkv_account_id']);
            $table->dropIndex(['property_type']);
            $table->dropIndex(['max_guests']);
            
            $table->dropColumn([
                'zip_code',
                'country',
                'property_type',
                'max_guests',
                'description',
                'main_image',
                'amenities',
                'photos',
                'summary_data',
                'bookerville_id',
                'bkv_account_id',
                'manager_first_name',
                'manager_last_name',
                'manager_phone',
                'business_name',
                'email_address_account',
                'off_line',
                'property_details_api_url',
                'bookerville_last_update',
                'bookerville_created_at',
                'bookerville_updated_at'
            ]);
        });
    }
};
