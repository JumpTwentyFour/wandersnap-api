<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Locations\Models\Location;
use App\Filament\Resources\LocationResource\Pages;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('trip_id')
                    ->relationship(name: 'trip', titleAttribute: 'name')
                    ->required(),
                TextInput::make('name')->required(),
                TextInput::make('latitude')->numeric()
                    ->inputMode('decimal')->required(),
                TextInput::make('longitude')->numeric()
                    ->inputMode('decimal')->required(),
                Repeater::make('images')
                    ->relationship()
                    ->simple(
                        FileUpload::make('path')
                            ->disk(config()->get('filament.location_images_filesystem'))
                            ->validationAttribute('image')
                            ->preserveFilenames()
                            ->visibility('private')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->required(),
                    )
                    ->defaultItems(0)
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        $data['name'] = basename($data['path']);
                 
                        return $data;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('trip.name')
                    ->label('Trip')
                    ->weight(FontWeight::Bold)
                    ->url(fn (Location $record): string => route('filament.admin.resources.trips.edit', $record->trip_id)),
                Tables\Columns\TextColumn::make('name')->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('longitude'),
                Tables\Columns\TextColumn::make('latitude'),
                Tables\Columns\TextColumn::make('images_count')->counts('images'),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('trip')
                    ->multiple()
                    ->relationship('trip', 'name')
                    ->preload(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocation::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
