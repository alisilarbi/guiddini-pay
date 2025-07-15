<?php

namespace App\Filament\Partner\Pages;

use App\Models\User;
use App\Models\License;
use App\Models\Prospect;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Application;
use Illuminate\Support\Str;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Contracts\HasTable;
use App\Actions\Prospect\DeleteProspect;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Actions\Prospect\ConvertProspect;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Prospects extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static string $view = 'filament.partner.pages.prospects';
    protected static ?string $navigationGroup = 'CRM';
    protected static ?int $navigationSort = 8;


    public function table(Table $table): Table
    {
        return $table
            ->query(Prospect::where('partner_id', Auth::user()->id))
            ->columns([

                TextColumn::make('name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable()
                    ->color('primary'),

                TextColumn::make('company_name')
                    ->label('Nom de l\'entreprise')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function (Prospect $record) {
                        return $record->legal_status . ' ' . $record->company_name;
                    }),

                TextColumn::make('phone')
                    ->label('Numéro de téléphone')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                ViewColumn::make('bank_account')
                    ->label('Compte bancaire')
                    ->view('components.tables.columns.bank_account'),

                ViewColumn::make('integrations')
                    ->label('Intégrations')
                    ->view('components.tables.columns.integrations'),

                TextColumn::make('website_link')
                    ->label('Lien du site web')
                    ->limit(30)
                    ->url(fn($record) => $record?->website_link, true)
                    ->hidden(fn($record) => !$record?->website_integration),

                TextColumn::make('programming_languages')
                    ->label('Languages de programmation')
                    ->formatStateUsing(fn($state) => implode(', ', json_decode($state ?? '[]')))
                    ->badge()
                    ->color('warning'),

            ])
            ->filters([
                SelectFilter::make('legal_status')
                    ->label('Statut juridique')
                    ->options([
                        'EURL' => 'EURL',
                        'SARL' => 'SARL',
                        'SPA' => 'SPA',
                        'SPAS' => 'SPAS',
                        'SPASU' => 'SPASU',
                        'SNC' => 'SNC',
                        'SCS' => 'SCS',
                        'SCA' => 'SCA',
                        'EPIC' => 'EPIC',
                        'GR' => 'GR',
                        'Auto-Entrepreneur' => 'Auto-Entrepreneur',
                        'Association' => 'Association',
                        'Natural-Person' => 'Personne-Physique',
                        'Liberal-Profession' => 'Profession-Libéral',
                    ])
                    ->searchable(),

                TernaryFilter::make('converted')
                    ->label('Converti')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->default(false)
                    ->queries(
                        true: fn(Builder $query) => $query->where('converted', true),
                        false: fn(Builder $query) => $query->where('converted', false),
                    ),
            ])
            ->actions([

                ActionGroup::make([
                    Action::make('convert')
                        ->label('Convertir')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-arrow-path')
                        ->disabled(fn(Prospect $prospect) => $prospect->converted)
                        ->action(function (Prospect $prospect, ConvertProspect $convertProspect) {

                            $convertProspect->handle(
                                partner: Auth::user(),
                                prospect: $prospect,
                            );

                            Notification::make()
                                ->title('prospect converted')
                                ->success()
                                ->send();

                            $this->dispatch('refresh-table');
                        }),

                    Action::make('delete')
                        ->label('Supprimer')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->action(function (Prospect $prospect, DeleteProspect $deleteProspect) {

                            $deleteProspect->handle($prospect);
                            Notification::make()
                                ->title('Prospect deleted')
                                ->success()
                                ->send();
                            $this->dispatch('refresh-table');
                            // $prospect->delete();
                        }),

                ])



            ])
            ->headerActions([])
            ->paginated([25, 50, 75, 100, 'all']);;
    }
}
