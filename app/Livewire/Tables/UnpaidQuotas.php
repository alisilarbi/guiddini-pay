<?php

namespace App\Livewire\Tables;

use App\Models\User;
use Filament\Tables;
use App\Models\Quota;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\QuotaTransaction;
use Illuminate\Contracts\View\View;
use App\Traits\HandlesWebExceptions;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Services\InternalPayments\InternalPaymentService;

class UnpaidQuotas extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    use HandlesWebExceptions;

    public User $partner;
    public $total;
    public $unpaidQuotas;

    protected InternalPaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = app(InternalPaymentService::class);
        $this->unpaidQuotas = Quota::where('partner_id', Auth::user()->id)->where('payment_status', 'unpaid')->get();
    }

    public function table(Table $table): Table
    {

        $this->partner = Auth::user();
        $this->total = $this->unpaidQuotas->sum('total');

        return $table
            ->query(Quota::where('partner_id', $this->partner->id)->where('payment_status', 'unpaid'))
            ->columns([
                TextColumn::make('payment_status')
                    ->badge()
                    ->color('danger')
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                TextColumn::make('quantity')
                    ->numeric(),

                TextColumn::make('total')
                    ->numeric()
                    ->formatStateUsing(fn($state) => $state . ' DA'),

                TextColumn::make('updated_at')
                    ->dateTime(),
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
            ])
            ->paginated(false);
    }

    public function payDebts()
    {
        try {
            $ids = $this->unpaidQuotas->pluck('id')->toArray();
            $data = [
                'amount' => $this->total,
                'origin' => 'Quota Debt',
                'partner_id' => $this->partner->id,
                'transactions' => $ids,
            ];

            $result = $this->paymentService->initiatePayment(
                $data
            );

            // Notification::make()
            //     ->title('Paiement initié avec succès')
            //     ->success()
            //     ->send();

            return redirect()->to($result['formUrl']);
        } catch (\Throwable $e) {
            $this->handleWebException($e);
        }
    }

    public function render(): View
    {
        return view('livewire.tables.unpaid-quotas');
    }
}
