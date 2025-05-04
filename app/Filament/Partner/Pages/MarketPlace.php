<?php

namespace App\Filament\Partner\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Application;
use App\Models\Transaction;
use App\Models\EventHistory;
use App\Models\QuotaTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class MarketPlace extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;


    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static string $view = 'filament.partner.pages.market-place';
    public User $partner;
    public $totalApplications;
    public $paidApplications;
    public $unpaidApplications;
    public $remainingAllowance;
    public $newAllowance;
    public $applicationPrice;

    public function mount(): void
    {
        $this->partner = Auth::user();
        $this->applicationPrice = $this->partner->application_price;

        // Calculate application stats
        $this->totalApplications = Application::where('user_id', $this->partner->id)->count();
        $this->paidApplications = Application::where('user_id', $this->partner->id)
            ->where('payment_status', 'paid')
            ->count();
        $this->unpaidApplications = $this->totalApplications - $this->paidApplications;
        $this->remainingAllowance = $this->partner->remaining_allowance;
        $this->newAllowance = 1;
    }

    public function table(Table $table): Table
    {
        $partnerId = $this->partner->id;
        return $table
            ->striped()
            ->heading('Historique')
            ->query(EventHistory::where('partner_id', $this->partner->id))
            ->columns([
                TextColumn::make('event_summary')
                    ->label('')
                    ->badge()
                    ->color(function ($record) {
                        if ($record->event_type === 'application')
                            return 'info';
                        else if ($record->event_type === 'quota')
                            return 'success';
                    }),

                TextColumn::make('action')
                    ->label('')
                    // ->badge()
                    ->formatStateUsing(function ($record) {
                        if ($record->event_code === 'application_creation')
                            return $record->application->name;
                        else if ($record->event_code === 'quota_creation')
                            return $record->action;
                    })
                    ->color(function ($record) {
                        if ($record->event_type === 'application')
                            return 'info';
                        else if ($record->event_type === 'quota')
                            return 'success';
                    }),

                // TextColumn::make('payment_status')
                //     ->label('')
                //     ->formatStateUsing(fn($state) => ucfirst($state))
                //     ->color(function ($state) {
                //         if ($state === 'unpaid')
                //             return 'danger';
                //         else if ($state === 'paid')
                //             return 'success';
                //     })
                //     ->badge()
                //     ->sortable(),

                // TextColumn::make('total')
                //     ->label('')
                //     // ->money('DZ')
                //     ->formatStateUsing(fn($record) => $record->total . ' DA')
                //     // ->badge()
                //     ->color('primary')
                //     ->sortable(),

                TextColumn::make('details')
                    ->label('')
                    ->formatStateUsing(function ($state, $record) {
                        if ($state === $record->details['price'])
                            return $state . ' DA';

                        if ($state === $record->details['quantity'])
                            return $state . ' App';

                        if ($state === $record->details['payment_status'])
                            return ucfirst($state);
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                // Add actions if needed
            ])
            ->bulkActions([
                // Add bulk actions if needed
            ]);
    }
}
