<?php

namespace App\Filament\User\Pages;

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

    protected static string $view = 'filament.user.pages.transactions';

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::query())
            ->columns([
                TextColumn::make('pack_name')
                    ->label('Ref Produit'),

                TextColumn::make('price')
                    ->label('Prix'),

                TextColumn::make('name')
                    ->label('Nom du client'),

                TextColumn::make('email')
                    ->label('Email'),

                TextColumn::make('phone')
                    ->label('Téléphone'),

                TextColumn::make('client_order_id'),

                TextColumn::make('gateway_order_id'),

                TextColumn::make('gateway_bool'),

                TextColumn::make('gateway_response_message'),

                TextColumn::make('gateway_error_code'),

                TextColumn::make('gateway_code'),

                TextColumn::make('gateway_code'),


            ])
            ->filters([
            ])
            ->actions([
            ])
            ->headerActions([
            ])
            ->bulkActions([
            ]);
    }
}
