<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationGroup = 'company';

    protected static ?string $navigationIcon = 'heroicon-o-office-building';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('personal_company')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Creator')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('personal_company')
                    ->label('Personal Company')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // filter by personal_id
                Tables\Filters\SelectFilter::make('personal_company')
                    ->label('Personal Company')
                    ->options([
                        'true' => 'Personal Company',
                        'false' => 'Not Personal Company',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Users')
                    ->relationship('owner', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
                \pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction::make(),
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
            'index' => Pages\ListCompanies::route('/'),
            // 'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
    public static function getEloquentQuery(): EloquentBuilder
    {
        $user = auth()->user();
        $isSuperAdmin = $user instanceof \App\Models\User && $user->isSuperAdmin();

        if (! $isSuperAdmin)
            return parent::getEloquentQuery()
                ->with(['owner:id,name'])
                ->where('id', $user?->current_company_id);

        return parent::getEloquentQuery()
            ->with(['owner:id,name']);
    }
}
