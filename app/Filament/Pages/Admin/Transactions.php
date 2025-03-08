<?php

namespace App\Filament\Pages\Admin;

use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Transaction;

use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Transactions extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.admin.transactions';

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::query())
            ->columns([
                TextColumn::make('updated_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    // ->since()
                    ->color('gray')
                    ->description(fn($record) => $record->updated_at->diffForHumans()),

                TextColumn::make('amount')
                    // ->money('DZD')
                    ->suffix(' DZD')
                    ->color(fn($record) => $record->amount > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignRight(),

                TextColumn::make('order_id')
                    ->copyable()
                    ->searchable()
                    ->label('Order ID')
                    ->color('primary'),

                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'success' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => strtoupper($state)),

                TextColumn::make('action_code_description')
                    ->label('Description')
                    ->wrap()
                    ->limit(50),

                TextColumn::make('license_env')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'production' => 'success',
                        'staging' => 'warning',
                        default => 'gray',
                    })
                    ->label('Environment'),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->copyable()
                    ->color('gray'),

                TextColumn::make('form_url')
                    ->label('')
                    ->formatStateUsing(fn() => '')
                    ->url(fn($record) => $record->form_url)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-link')
                    // ->iconSize('sm')
                    ->color('gray')
                    ->tooltip('Payment Link'),

                // TextColumn::make('form_url')
                //     ->label('Payment Link')
                //     ->url(fn($record) => $record->form_url)
                //     ->openUrlInNewTab()
                //     ->icon('heroicon-o-link')
                //     ->color('primary'),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                // Add actions if needed
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->deferLoading()
            ->paginated([10, 25, 50, 100]);
    }
}
