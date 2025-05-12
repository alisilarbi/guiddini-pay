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
use App\Traits\HandlesWebExceptions;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Services\InternalPayments\InternalPaymentService;

class Marketplace extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HandlesWebExceptions;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $title = 'MarketPlace';

    public function getHeading(): string
    {
        if($this->orderNumber)
            return 'Paiement en ligne';
        else
            return 'MarketPlace';
    }

    protected static ?string $navigationLabel = 'MarketPlace';
    protected static ?string $slug = 'marketplace';

    protected static string $view = 'filament.partner.pages.marketplace';
    public $totalApplications;
    public $paidApplications;
    public $unpaidApplications;
    public $remainingAllowance;
    public $newAllowance;
    public $applicationPrice;
    public $orderNumber;
    public $transaction;
    public User $partner;
    protected InternalPaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = app(InternalPaymentService::class);
        // $this->receiptService = app(ReceiptService::class);
    }

    public function mount(): void
    {
        $this->orderNumber = request()->get('orderNumber');
        if($this->orderNumber)
            $this->transaction = Transaction::where('order_number', $this->orderNumber)->first();
        $this->partner = User::where('id', Auth::user()->id)->first();
        $this->applicationPrice = $this->partner->application_price;
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

    public function tryAgain()
    {
        return redirect('partner/marketplace');
    }
}
