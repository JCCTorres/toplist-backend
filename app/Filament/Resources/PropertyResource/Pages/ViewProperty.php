<?php

namespace App\Filament\Resources\PropertyResource\Pages;

use App\Filament\Resources\PropertyResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Support\Enums\FontWeight;

class ViewProperty extends ViewRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar'),
        ];
    }

    public function getTitle(): string
    {
        return 'Visualizar Propriedade';
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informações Básicas')
                    ->schema([
                        ImageEntry::make('main_image')
                            ->label('Imagem Principal')
                            ->height(200)
                            ->columnSpanFull()
                            ->visibility('public')
                            ->disk('public'),

                        TextEntry::make('property_id')
                            ->label('ID da Propriedade')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('title')
                            ->label('Título')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('owner')
                            ->label('Proprietário'),

                        TextEntry::make('property_type')
                            ->label('Tipo de Propriedade')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'apartment' => 'Apartamento',
                                'house' => 'Casa',
                                'condo' => 'Condomínio',
                                'villa' => 'Vila',
                                'townhouse' => 'Townhouse',
                                'resort' => 'Resort',
                                default => ucfirst($state),
                            }),

                        TextEntry::make('max_guests')
                            ->label('Máximo de Hóspedes')
                            ->suffix(' hóspedes'),

                        TextEntry::make('description')
                            ->label('Descrição')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Localização')
                    ->schema([
                        TextEntry::make('address')
                            ->label('Endereço'),

                        TextEntry::make('house_number')
                            ->label('Número'),

                        TextEntry::make('zip_code')
                            ->label('CEP'),

                        TextEntry::make('country')
                            ->label('País'),
                    ])
                    ->columns(2),

                Section::make('Dados Bookerville')
                    ->schema([
                        TextEntry::make('bookerville_id')
                            ->label('Bookerville ID'),

                        TextEntry::make('airbnb_id')
                            ->label('Airbnb ID'),

                        TextEntry::make('manager_first_name')
                            ->label('Nome do Gerente'),

                        TextEntry::make('manager_last_name')
                            ->label('Sobrenome do Gerente'),

                        TextEntry::make('manager_phone')
                            ->label('Telefone'),

                        TextEntry::make('email_address_account')
                            ->label('Email'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Galeria de Fotos')
                    ->schema([
                        ImageEntry::make('photos')
                            ->label('')
                            ->columnSpanFull()
                            ->stacked()
                            ->limit(6)
                            ->visibility('public')
                            ->disk('public'),
                    ])
                    ->collapsible(),

                Section::make('Observações')
                    ->schema([
                        TextEntry::make('observations')
                            ->label('')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->hidden(fn ($record) => empty($record->observations)),

                Section::make('Datas')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i:s'),

                        TextEntry::make('updated_at')
                            ->label('Atualizado em')
                            ->dateTime('d/m/Y H:i:s'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}