<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PropertyResource\Pages;
use App\Models\Property;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Propriedades';

    protected static ?string $modelLabel = 'Propriedade';

    protected static ?string $pluralModelLabel = 'Propriedades';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações Básicas')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('property_id')
                                    ->label('ID da Propriedade')
                                    ->required()
                                    ->readOnly()
                                    ->unique(ignoreRecord: true),

                                TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->readOnly()
                                    ->maxLength(255),

                                TextInput::make('airbnb_id')
                                    ->label('Airbnb Listing ID')
                                    ->helperText('Airbnb listing ID used to build the checkout URL. Find it in the Airbnb listing URL: airbnb.com/rooms/{ID}')
                                    ->maxLength(30)
                                    ->placeholder('e.g. 6629060'),

                                TextInput::make('owner')
                                    ->label('Proprietário')
                                    ->maxLength(255),
                            ]),

                        Forms\Components\Grid::make(1)
                            ->schema([
                                Textarea::make('description')
                                    ->label('Descrição')
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Textarea::make('observations')
                                    ->label('Observações')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Localização')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                TextInput::make('address')
                                    ->label('Endereço')
                                    ->maxLength(255),

                                TextInput::make('zip_code')
                                    ->label('CEP')
                                    ->maxLength(20),

                                TextInput::make('country')
                                    ->label('País')
                                    ->default('US')
                                    ->maxLength(10),
                            ]),

                        TextInput::make('house_number')
                            ->label('Número da Casa')
                            ->maxLength(50),
                    ]),

                Section::make('Detalhes da Propriedade')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Select::make('property_type')
                                    ->label('Tipo de Propriedade')
                                    ->options([
                                        'apartment' => 'Apartment',
                                        'house' => 'House',
                                        'condo' => 'Condo',
                                        'villa' => 'Villa',
                                        'townhouse' => 'Townhouse',
                                        'resort' => 'Resort',
                                        'other' => 'Other'
                                    ])
                                    ->searchable(),

                                TextInput::make('max_guests')
                                    ->label('Máximo de Hóspedes')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(20),
                            ]),
                    ]),

                Section::make('Imagens')
                    ->schema([
                        Forms\Components\Tabs::make('main_image_tabs')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('main_image_current')
                                    ->label('Imagem Principal Atual')
                                    ->schema([
                                        // TextInput::make('main_image')
                                        //     ->label('URL da Imagem Principal')
                                        //     ->url()
                                        //     ->maxLength(500)
                                        //     ->live(onBlur: true)
                                        //     ->suffixAction(
                                        //         Forms\Components\Actions\Action::make('preview_main')
                                        //             ->icon('heroicon-o-eye')
                                        //             ->tooltip('Visualizar imagem principal')
                                        //             ->action(function ($state) {
                                        //                 // O preview será mostrado automaticamente abaixo
                                        //             })
                                        //     )
                                        //     ->columnSpanFull()
                                        //     ->helperText('URL da imagem principal (local ou externa)'),

                                        // Forms\Components\Section::make('Preview da Imagem Principal')
                                        //     ->schema([
                                        //         Forms\Components\Placeholder::make('main_image_preview')
                                        //             ->label('')
                                        //             ->content(function ($record, $get) {
                                        //                 $mainImage = $get('main_image') ?? $record?->main_image;
                                                        
                                        //                 if (!$mainImage) {
                                        //                     return 'Nenhuma imagem principal cadastrada.';
                                        //                 }
                                                        
                                        //                 $imageUrl = $mainImage;
                                                        
                                        //                 // Se for arquivo local, criar URL completa
                                        //                 if (strpos($mainImage, 'storage/') === 0) {
                                        //                     $imageUrl = asset($mainImage);
                                        //                 } elseif (strpos($mainImage, 'property-images/') === 0) {
                                        //                     $imageUrl = asset('storage/' . $mainImage);
                                        //                 }
                                                        
                                        //                 $html = '<div style="max-width: 400px; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: white;">';
                                        //                 $html .= '<img src="' . $imageUrl . '" style="width: 100%; height: 250px; object-fit: cover; display: block;" loading="lazy" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';">';
                                        //                 $html .= '<div style="display: none; padding: 2rem; text-align: center; color: #6b7280; background: #f9fafb;">Erro ao carregar imagem principal</div>';
                                        //                 $html .= '<div style="padding: 0.75rem; font-size: 0.875rem; color: #6b7280; word-break: break-all;">';
                                        //                 $html .= '<strong>URL:</strong> ' . htmlspecialchars($mainImage);
                                        //                 $html .= '</div>';
                                        //                 $html .= '</div>';
                                                        
                                        //                 return new \Illuminate\Support\HtmlString($html);
                                        //             })
                                        //             ->columnSpanFull(),
                                        //     ])
                                        //     ->collapsible()
                                        //     ->collapsed(false),
                                    ]),

                                Forms\Components\Tabs\Tab::make('main_image_upload')
                                    ->label('Upload Nova Imagem Principal')
                                    ->schema([
                                        FileUpload::make('new_main_image')
                                            ->label('Upload Nova Imagem Principal')
                                            ->image()
                                            ->disk('public')
                                            ->directory('property-images')
                                            ->visibility('public')
                                            ->maxSize(1024) // 1MB
                                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                                            ->imagePreviewHeight('250')
                                            ->panelAspectRatio('2:1')
                                            ->deletable()
                                            ->downloadable()
                                            ->previewable()
                                            ->columnSpanFull()
                                            ->helperText('Faça upload de uma nova imagem principal. Ela substituirá a atual.')
                                            ->afterStateUpdated(function ($state, $set) {
                                                if ($state) {
                                                    $set('main_image', $state);
                                                }
                                            }),
                                    ]),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Tabs::make('photos_management')
                            ->tabs([
                                Forms\Components\Tabs\Tab::make('photos_gallery')
                                    ->label('Galeria Atual')
                                    ->schema([
                                        // Forms\Components\Repeater::make('photos')
                                        //     ->label('Galeria de Fotos')
                                        //     ->simple(
                                        //         TextInput::make('url')
                                        //             ->label('URL da Imagem')
                                        //             ->url()
                                        //             ->required()
                                        //             ->live(onBlur: true)
                                        //             ->suffixAction(
                                        //                 Forms\Components\Actions\Action::make('preview')
                                        //                     ->icon('heroicon-o-eye')
                                        //                     ->tooltip('Visualizar imagem')
                                        //                     ->action(function ($state) {
                                        //                         // O preview será mostrado automaticamente
                                        //                     })
                                        //             )
                                        //             ->afterStateUpdated(function ($state, $component) {
                                        //                 // Validar se é uma URL válida de imagem
                                        //                 if ($state && filter_var($state, FILTER_VALIDATE_URL)) {
                                        //                     $component->state($state);
                                        //                 }
                                        //             })
                                        //     )
                                        //     // ->addActionLabel('Adicionar Imagem')
                                        //     ->reorderable()
                                        //     ->collapsible()
                                        //     ->itemLabel(fn (array $state): ?string => 
                                        //         isset($state['url']) ? 
                                        //             (strlen($state['url']) > 50 ? 
                                        //                 substr($state['url'], 0, 50) . '...' : 
                                        //                 $state['url']
                                        //             ) : 
                                        //             'Nova imagem'
                                        //     )
                                        //     ->columnSpanFull(),
                                                // ->helperText('Todas as imagens da propriedade (uploads locais e URLs externas)'),
                                        
                                        Forms\Components\Section::make('Preview das Imagens')
                                            ->schema([
                                                Forms\Components\Placeholder::make('photos_preview')
                                                    ->label('')
                                                    ->content(function ($record) {
                                                        if (!$record || !$record->photos) {
                                                            return 'Nenhuma imagem cadastrada.';
                                                        }
                                                        
                                                        $html = '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem;">';
                                                        
                                                        foreach ($record->photos as $index => $photo) {
                                                            $imageUrl = $photo;
                                                            
                                                            // Se for arquivo local, criar URL completa
                                                            if (strpos($photo, 'storage/') === 0) {
                                                                $imageUrl = asset($photo);
                                                            } elseif (strpos($photo, 'property-images/') === 0) {
                                                                $imageUrl = asset('storage/' . $photo);
                                                            }
                                                            
                                                            $html .= '<div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: white;">';
                                                            $html .= '<img src="' . $imageUrl . '" style="width: 100%; height: 150px; object-fit: cover; display: block;" loading="lazy" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';">';
                                                            $html .= '<div style="display: none; padding: 2rem; text-align: center; color: #6b7280; background: #f9fafb;">Erro ao carregar imagem</div>';
                                                            $html .= '<div style="padding: 0.5rem; font-size: 0.75rem; color: #6b7280; word-break: break-all;">';
                                                            $html .= htmlspecialchars(strlen($photo) > 40 ? substr($photo, 0, 40) . '...' : $photo);
                                                            $html .= '</div>';
                                                            $html .= '</div>';
                                                        }
                                                        
                                                        $html .= '</div>';
                                                        
                                                        return new \Illuminate\Support\HtmlString($html);
                                                    })
                                                    ->columnSpanFull(),
                                            ])
                                            ->collapsible()
                                            ->collapsed(false),
                                    ]),

                                Forms\Components\Tabs\Tab::make('photos_upload')
                                    ->label('Upload Novas Fotos')
                                    ->schema([
                                        FileUpload::make('new_photos')
                                            ->label('Upload de Novas Fotos')
                                            ->multiple()
                                            ->image()
                                            ->disk('public')
                                            ->directory('property-images')
                                            ->visibility('public')
                                            ->maxSize(1024) // 1MB por arquivo
                                            ->maxFiles(10) // Máximo 10 fotos
                                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png'])
                                            ->imagePreviewHeight('200')
                                            ->panelAspectRatio('3:2')
                                            ->reorderable()
                                            ->deletable()
                                            ->downloadable()
                                            ->previewable()
                                            ->columnSpanFull()
                                            ->helperText('Faça upload de novas imagens. Elas serão adicionadas à galeria existente.')
                                            ->afterStateUpdated(function ($state, $record, $set, $get) {
                                                if ($state && $record) {
                                                    $currentPhotos = $get('photos') ?: [];
                                                    $newPhotos = is_array($state) ? $state : [$state];
                                                    
                                                    foreach ($newPhotos as $photo) {
                                                        if (!in_array($photo, $currentPhotos)) {
                                                            $currentPhotos[] = $photo;
                                                        }
                                                    }
                                                    
                                                    $set('photos', $currentPhotos);
                                                }
                                            }),
                                    ]),
                             
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Dados Bookerville')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                TextInput::make('bookerville_id')
                                    ->label('Bookerville ID')
                                    ->maxLength(100),

                                TextInput::make('bkv_account_id')
                                    ->label('BKV Account ID')
                                    ->maxLength(100),

                                TextInput::make('property_details_api_url')
                                    ->label('API URL')
                                    ->url()
                                    ->maxLength(500),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('manager_first_name')
                                    ->label('Nome do Gerente')
                                    ->maxLength(100),

                                TextInput::make('manager_last_name')
                                    ->label('Sobrenome do Gerente')
                                    ->maxLength(100),

                                TextInput::make('manager_phone')
                                    ->label('Telefone do Gerente')
                                    ->tel()
                                    ->maxLength(20),

                                TextInput::make('email_address_account')
                                    ->label('Email da Conta')
                                    ->email()
                                    ->maxLength(255),
                            ]),

                        TextInput::make('business_name')
                            ->label('Nome do Negócio')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('off_line')
                            ->label('Offline'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('URLs')
                    ->schema([
                        TextInput::make('airbnb_url')
                            ->label('URL do Airbnb')
                            ->url()
                            ->maxLength(500),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ImageColumn::make('main_image')
                //     ->label('Imagem')
                //     ->size(80)
                //     ->defaultImageUrl(url('/images/no-image.png')),

                TextColumn::make('property_id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (Property $record): ?string {
                        return $record->title;
                    }),

                TextColumn::make('property_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'apartment' => 'gray',
                        'house' => 'success',
                        'condo' => 'info',
                        'villa' => 'warning',
                        'townhouse' => 'primary',
                        'resort' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'apartment' => 'Apartament',
                        'house' => 'House',
                        'condo' => 'Condo',
                        'villa' => 'Villa',
                        'townhouse' => 'Townhouse',
                        'resort' => 'Resort',
                        default => ucfirst($state),
                    }),

                TextColumn::make('address')
                    ->label('Endereço')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(function (Property $record): ?string {
                        return $record->address;
                    }),

                TextColumn::make('max_guests')
                    ->label('Hóspedes')
                    ->alignCenter()
                    ->sortable(),

                // TextColumn::make('owner')
                //     ->label('Proprietário')
                //     ->searchable()
                //     ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('airbnb_id')
                    ->label('Airbnb ID')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not linked')
                    ->color(fn (?string $state): string => $state ? 'success' : 'danger')
                    ->icon(fn (?string $state): string => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->toggleable(),

                TextColumn::make('bookerville_id')
                    ->label('Bookerville ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // ->filters([
            //     SelectFilter::make('property_type')
            //         ->label('Tipo de Propriedade')
            //         ->options([
            //             'apartment' => 'Apartamento',
            //             'house' => 'Casa',
            //             'condo' => 'Condomínio',
            //             'villa' => 'Vila',
            //             'townhouse' => 'Townhouse',
            //             'resort' => 'Resort',
            //         ]),

            //     SelectFilter::make('max_guests')
            //         ->label('Máximo de Hóspedes')
            //         ->options([
            //             '1' => '1 hóspede',
            //             '2' => '2 hóspedes',
            //             '4' => '4 hóspedes',
            //             '6' => '6 hóspedes',
            //             '8' => '8 hóspedes',
            //             '10' => '10+ hóspedes',
            //         ])
            //         ->query(function (Builder $query, array $data): Builder {
            //             return $query
            //                 ->when(
            //                     $data['value'] === '10',
            //                     fn (Builder $query, $value): Builder => $query->where('max_guests', '>=', 10),
            //                     fn (Builder $query): Builder => $query->where('max_guests', $data['value'])
            //                 );
            //         }),

            //     Tables\Filters\TrashedFilter::make(),
            // ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperties::route('/'),
            'create' => Pages\CreateProperty::route('/create'),
            'view' => Pages\ViewProperty::route('/{record}'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
