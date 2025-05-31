<?php

namespace App\Filament\Partner\Pages;

use App\Models\User;
use App\Models\Quota;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Application;
use App\Models\Transaction;
use App\Models\EventHistory;
use Filament\Actions\Action;
use App\Models\QuotaTransaction;
use App\Actions\Quota\MarkAsPaid;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use App\Actions\Quota\PurchaseQuota;
use App\Traits\HandlesWebExceptions;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Services\InternalPayments\ReceiptService;
use Filament\Actions\Concerns\InteractsWithActions;
use App\Services\InternalPayments\InternalPaymentService;

class Marketplace extends Page implements HasForms, HasTable, HasActions
{
    use InteractsWithTable;
    use InteractsWithForms;
    use InteractsWithActions;
    use HandlesWebExceptions;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $title = 'MarketPlace';

    public function getHeading(): string
    {
        if ($this->orderNumber)
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
    public $quotas;
    protected $markAsPaidAction;
    public string $email;

    protected ReceiptService $receiptService;

    protected $purchaseQuotaAction;

    protected bool $viewTransactions = false;

    public function __construct()
    {
        $this->paymentService = app(InternalPaymentService::class);
        $this->markAsPaidAction = app(MarkAsPaid::class);
        $this->receiptService = app(ReceiptService::class);
        $this->purchaseQuotaAction = app(PurchaseQuota::class);
    }

    public function mount(): void
    {
        $this->orderNumber = request('orderNumber');
        $this->partner = Auth::user();
        $this->applicationPrice = $this->partner->application_price;

        if ($this->orderNumber) {
            $this->transaction = Transaction::where('order_number', $this->orderNumber)->first();

            if (!$this->transaction) {
                $this->orderNumber = null;
                return;
            }

            if ($this->transaction->status === 'paid') {
                if ($this->transaction->origin === 'Quota Debt') {
                    $this->quotas = Quota::whereIn('id', $this->transaction->quota_transactions)->get();
                    $this->markAsPaidAction->handle($this->quotas);
                }

                if ($this->transaction->origin === 'Quota Credit') {
                    $this->purchaseQuotaAction->handle($this->transaction, $this->partner);
                }

                Notification::make()->title('Paiement réussi')->success()->send();
            } else {
                Notification::make()
                    ->title('Erreur de paiement')
                    ->danger()
                    ->body($this->transaction->action_code_description ?? 'Une erreur est survenue lors du traitement de votre paiement.')
                    ->send();
            }
        }
    }

    public function table(Table $table): Table
    {
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
                    ->formatStateUsing(function ($record) {
                        if ($record->event_code === 'application_creation')
                            return $record->application?->name;
                        // return 'heheeh';
                        else if ($record->event_code === 'quota_creation')
                            return $record->action;
                        else if ($record->event_code === 'quota_paid')
                            return $record->action;
                        else if ($record->event_code === 'quota_bought')
                            return $record->action;
                    })
                    ->color(function ($record) {
                        if ($record->event_type === 'application')
                            return 'info';
                        else if ($record->event_type === 'quota')
                            return 'success';
                        else if ($record->event_type === 'quota_paid')
                            return 'success';
                        else if ($record->event_type === 'quota_bought')
                            return 'warning';
                    }),

                TextColumn::make('details')
                    ->label('')
                    ->formatStateUsing(function ($state, $record) {
                        $details = $record->details ?? [];

                        if (($details['price'] ?? null) === $state)
                            return $state . ' DA';

                        if (($details['total'] ?? null) === $state)
                            return $state . ' DA';

                        if (($details['quantity'] ?? null) === $state)
                            return $state . ' App';

                        if (($details['payment_status'] ?? null) === $state)
                            return ucfirst($state);
                    })
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('')
                    ->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public function tryAgain()
    {
        return redirect('partner/marketplace');
    }

    public function viewTransaction()
    {
        return $this->viewTransactions = !$this->viewTransactions;
    }

    public function downloadReceipt()
    {

        try {
            $signedUrl = $this->receiptService->generateDownloadLink($this->orderNumber);
            $this->dispatch('download-receipt', url: $signedUrl);

            Notification::make()
                ->title('Reçu téléchargé avec succès')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->handleWebException($e);
        }
    }

    public function sendEmail()
    {
        $this->validate([
            'orderNumber' => 'required|string',
            'email' => 'required|email',
        ], [
            'orderNumber.required' => 'Le numéro de commande est requis.',
            'email.required' => 'L\'adresse e-mail est requise.',
            'email.email' => 'L\'adresse e-mail doit être valide.',
        ]);

        $data = [
            'orderNumber' => $this->orderNumber,
            'email' => $this->email,
        ];

        $this->receiptService->emailPaymentReceipt($data);
        $this->dispatch('close-modal', id: 'send-email');

        Notification::make()
            ->title('Email envoyé avec succès')
            ->success()
            ->send();

        try {
        } catch (\Throwable $e) {
            $this->handleWebException($e);
        }
    }
}
