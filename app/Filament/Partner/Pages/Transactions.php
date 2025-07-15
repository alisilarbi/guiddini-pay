<?php

namespace App\Filament\Partner\Pages;

use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Application;

use App\Models\Transaction;
use Tables\Actions\ViewAction;
use Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\SelectColumn;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Transactions extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static string $view = 'filament.partner.pages.transactions';
    protected static ?string $navigationGroup = 'Finances';
    protected static ?int $navigationSort = 9;


    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::where('partner_id', Auth::user()->id))
            ->columns([
                TextColumn::make('application.name')
                    ->label('Nom de l\'application')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->application?->website_url)
                    ->toggleable(),

                TextColumn::make('application.user.name')
                    ->label('Propriétaire')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('amount')
                    ->label('Montant')
                    ->money('DZD', true)
                    ->sortable()
                    ->color(fn($record) => $record->amount > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('deposit_amount')
                    ->label('Dépôt')
                    ->money('DZD', true)
                    ->label('Deposit')
                    ->sortable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                // TextColumn::make('currency')
                //     ->badge()
                //     ->color('gray')
                //     ->searchable()
                //     ->toggleable()
                //     ->state(fn($record) => 'DZD'),

                TextColumn::make('order_id')
                    ->label('Numéro d\'identification')
                    ->copyable()
                    ->searchable()
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('order_number')
                    ->label('Numéro de commande')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->colors([
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                    ])
                    ->toggleable(),

                TextColumn::make('confirmation_status')
                    ->label('Statut de confirmation')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn($record) => $record->description)
                    ->toggleable(),

                TextColumn::make('action_code')
                    ->label('Code d\'action')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('action_code_description')
                    ->label('Description du code d\'action')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('auth_code')
                    ->label('Code d\'authentification')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approval_code')
                    ->label('Code d\'approbation')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('error_code')
                    ->label('Code d\'erreur')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('error_message')
                    ->label('Message d\'erreur')
                    ->color('danger')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('card_holder_name')
                    ->label('Nom du titulaire de la carte')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('pan')
                    ->label('Numéro de carte')
                    ->formatStateUsing(fn($state) => '****-****-****-' . substr($state, -4))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('license_env')
                    ->label('Environment')
                    // ->color(fn(string $state): string => match ($state) {
                    //     'production' => 'success',
                    //     'staging' => 'warning',
                    //     default => 'gray',
                    // })
                    ->toggleable(),

                TextColumn::make('ip_address')
                    ->label('Adresse IP')
                    ->copyable()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('form_url')
                    ->label('Lien du formulaire')
                    ->url(fn($record) => $record->form_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('svfe_response')
                    ->label('SVFE Response')
                    // ->json()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Date de création')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Date de mise à jour')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->color('gray')
                    ->description(fn($record) => '(' . $record->updated_at->diffForHumans() . ')')
                    ->toggleable(),
            ])
            ->filters([
                // SelectFilter::make('status')
                //     ->options([
                //         'success' => 'Success',
                //         'pending' => 'Pending',
                //         'failed' => 'Failed',
                //     ]),
                // Tables\Filters\SelectFilter::make('license_env')
                //     ->options([
                //         'production' => 'Production',
                //         'staging' => 'Staging',
                //     ]),
            ])
            ->actions([
                // ViewAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->deferLoading()
            ->paginated([10, 25, 50, 100, 'all'])
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->recordClasses(fn($record) => match ($record->status) {
                'failed' => 'border-l-2 border-red-500',
                'pending' => 'border-l-2 border-yellow-500',
                'success' => 'border-l-2 border-green-500',
                default => null,
            });
    }
}
