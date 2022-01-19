<?php

namespace App\Filament\Resources;

use App\Enums\CongestionLevel;
use App\Enums\OccupancyStatus;
use App\Enums\ScheduleRelationship;
use App\Enums\VehicleStopStatus;
use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'gmdi-directions-bus';

    protected static ?string $recordTitleAttribute = 'vehicle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('')->columnSpan(2)->tabs([
                    Tabs\Tab::make('Static data')->schema([
                        TextInput::make('vehicle')
                            ->required(),
                        Toggle::make('active')
                            ->required(),
                        BelongsToSelect::make('agency_id')->required()->relationship('agency', 'name'),
                        Select::make('icon')->options(['bus', 'tram', 'train'])->required(),
                        TextInput::make('force_label'),
                    ]),
                    Tabs\Tab::make('Changing data')->schema([
                        TextInput::make('timestamp'),
                        TextInput::make('route')
                            ->required(),
                        TextInput::make('start'),
                        TextInput::make('gtfs_trip')->label('Trip (from feed)'),
                        TextInput::make('trip_id')->label('trip_id (relation)'),
                        TextInput::make('lat')->numeric(),
                        TextInput::make('lon')->numeric(),
                        TextInput::make('bearing')->numeric(),
                        TextInput::make('speed')->numeric(),
                        TextInput::make('stop_sequence')->numeric(),
                        TextInput::make('label'),
                        TextInput::make('plate'),
                        TextInput::make('odometer'),
                        Select::make('status')->options(VehicleStopStatus::asFlippedArray()),
                        Select::make('relationship')->options(ScheduleRelationship::asFlippedArray()),
                        Select::make('congestion')->options(CongestionLevel::asFlippedArray()),
                        Select::make('occupancy')->options(OccupancyStatus::asFlippedArray()),
                    ]),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('agency.short_name'),
                Tables\Columns\BooleanColumn::make('active'),
                Tables\Columns\TextColumn::make('icon'),
                Tables\Columns\TextColumn::make('displayed_label'),
                Tables\Columns\TextColumn::make('vehicle'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('M d, Y'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime('M d, Y'),
            ])
            ->filters([
                //
            ]);
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
