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

                TextColumn::make('license_env'),
                TextColumn::make('amount'),
                TextColumn::make('order_id'),
                TextColumn::make('order_number'),
                TextColumn::make('action_code_description'),
                TextColumn::make('action_code'),
                // TextColumn::make('status'),
                TextColumn::make('form_url'),
                TextColumn::make('ip_address'),
                TextColumn::make('updated_at'),

            ])
            ->filters([])
            ->actions([])
            ->headerActions([])
            ->bulkActions([]);
    }
}
