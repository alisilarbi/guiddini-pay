<?php

namespace App\Livewire\Tables;

use Filament\Tables;
use App\Models\License;
use Livewire\Component;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class LatestSatimLicenses extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(License::where('gateway_type', 'satim')->where('partner_id', Auth::user()->id)->latest())
            ->heading('License récentes')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom license')
                    ->weight(FontWeight::Bold),

                TextColumn::make('created_at')
                    ->label('Crée le')
                    ->color('danger')
                    ->badge(),

                TextColumn::make('successful_transactions_count')
                    ->label('Transactions réussites')
                    ->counts('successfulTransactions'),

            ])
            ->headerActions([
                Tables\Actions\Action::make('view')
                    ->label('Voir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->iconPosition(\Filament\Support\Enums\IconPosition::After)
                    ->color('danger')
                    ->outlined()
                    ->url(route('filament.partner.pages.satim-licenses')),
            ])
            ->defaultPaginationPageOption(5);
    }

    public function render(): View
    {
        return view('livewire.tables.latest-satim-licenses');
    }
}
