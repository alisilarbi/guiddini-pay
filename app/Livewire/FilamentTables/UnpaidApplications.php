<?php

namespace App\Livewire\FilamentTables;

use App\Models\User;
use Filament\Tables;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\Application;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Query\Builder;

use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class UnpaidApplications extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    public User $partner;

    public function table(Table $table): Table
    {
        $this->partner = Auth::user();

        return $table
            ->query(Application::where('partner_id', $this->partner->id)->where('payment_status', '!=', 'paid'))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('')
                    ->dateTime(),

                TextColumn::make('payment_status')
                    ->label('')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                TextColumn::make('quotaTransaction.application_price')
                    ->label('')
                    ->formatStateUsing(fn($state) => $state . ' DA')

            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.filament-tables.unpaid-applications');
    }
}
