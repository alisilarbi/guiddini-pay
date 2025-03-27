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

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::where('partner_id', Auth::user()->id))
            ->columns([
                TextColumn::make('application.name')
                    ->label('Application')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->application?->website_url)
                    ->toggleable(),

                TextColumn::make('amount')
                    ->money('DZD', true)
                    ->sortable()
                    ->color(fn($record) => $record->amount > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignRight()
                    ->toggleable(),

                TextColumn::make('deposit_amount')
                    ->money('DZD', true)
                    ->label('Deposit')
                    ->sortable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('currency')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('order_id')
                    ->copyable()
                    ->searchable()
                    ->label('Order ID')
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                SelectColumn::make('status')
                    ->options([
                        'success' => 'Success',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                    ])
                    ->searchable()
                    ->sortable()
                    // ->color(fn(string $state): string => match ($state) {
                    //     'success' => 'success',
                    //     'pending' => 'warning',
                    //     'failed' => 'danger',
                    //     default => 'gray',
                    // })
                    ->toggleable(),

                TextColumn::make('confirmation_status')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn($record) => $record->description)
                    ->toggleable(),

                TextColumn::make('action_code')
                    ->label('Action Code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('action_code_description')
                    ->label('Action Description')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('auth_code')
                    ->label('Auth Code')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('approval_code')
                    ->label('Approval Code')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('error_code')
                    ->label('Error Code')
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('error_message')
                    ->label('Error Message')
                    ->color('danger')
                    ->wrap()
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('card_holder_name')
                    ->label('Cardholder')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('pan')
                    ->label('Card Number')
                    ->formatStateUsing(fn($state) => '****-****-****-' . substr($state, -4))
                    ->toggleable(isToggledHiddenByDefault: true),

                SelectColumn::make('license_env')
                    ->options([
                        'production' => 'Production',
                        'staging' => 'Staging',
                    ])
                    ->label('Environment')
                    // ->color(fn(string $state): string => match ($state) {
                    //     'production' => 'success',
                    //     'staging' => 'warning',
                    //     default => 'gray',
                    // })
                    ->toggleable(),

                TextColumn::make('license_id')
                    ->label('License ID')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->copyable()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('form_url')
                    ->label('Payment Link')
                    ->url(fn($record) => $record->form_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->toggleable(),

                TextColumn::make('svfe_response')
                    ->label('SVFE Response')
                    ->json()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->since()
                    ->color('gray')
                    ->description(fn($record) => $record->updated_at->diffForHumans())
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

    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->query(Transaction::where('partner_id', Auth::user()->id))
    //         ->columns([

    //             TextColumn::make('application.name')
    //                 ->label('Application')
    //                 ->description(function (Transaction $record) {
    //                     return $record->application->website_url;
    //                 }),



    //             TextColumn::make('amount')
    //                 // ->money('DZD')
    //                 ->suffix(' DZD')
    //                 ->color(fn($record) => $record->amount > 0 ? 'success' : 'danger')
    //                 ->weight('bold')
    //                 ->alignRight(),

    //             TextColumn::make('order_id')
    //                 ->copyable()
    //                 ->searchable()
    //                 ->label('Order ID')
    //                 ->color('primary'),

    //             TextColumn::make('order_number')
    //                 ->label('Order #')
    //                 ->searchable(),

    //             TextColumn::make('status')
    //                 ->badge()
    //                 ->color(fn(string $state): string => match ($state) {
    //                     'success' => 'success',
    //                     'pending' => 'warning',
    //                     'failed' => 'danger',
    //                     default => 'gray',
    //                 })
    //                 ->formatStateUsing(fn($state) => strtoupper($state)),

    //             TextColumn::make('action_code_description')
    //                 ->label('Description')
    //                 ->wrap()
    //                 ->limit(50),

    //             TextColumn::make('license_env')
    //                 ->badge()
    //                 ->color(fn(string $state): string => match ($state) {
    //                     'production' => 'success',
    //                     'staging' => 'warning',
    //                     default => 'gray',
    //                 })
    //                 ->label('Environment'),

    //             TextColumn::make('ip_address')
    //                 ->label('IP Address')
    //                 ->copyable()
    //                 ->color('gray'),

    //             TextColumn::make('form_url')
    //                 ->label('Payment Link')
    //                 ->url(fn($record) => $record->form_url)
    //                 ->openUrlInNewTab()
    //                 ->icon('heroicon-o-link')
    //                 ->color('primary'),

    //             TextColumn::make('updated_at')
    //                 ->label('Date')
    //                 ->dateTime('M d, Y H:i')
    //                 ->sortable()
    //                 // ->since()
    //                 ->color('gray')
    //                 ->description(fn($record) => $record->updated_at->diffForHumans()),
    //         ])
    //         ->filters([
    //             // Add filters if needed
    //         ])
    //         ->actions([
    //             // Add actions if needed
    //         ])
    //         ->bulkActions([
    //             // Add bulk actions if needed
    //         ])
    //         ->defaultSort('updated_at', 'desc')
    //         ->striped()
    //         ->deferLoading()
    //         ->paginated([10, 25, 50, 100]);
    // }
}
