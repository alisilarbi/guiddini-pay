<?php

namespace App\Livewire\Tables;

use App\Models\User;
use Filament\Tables;
use Livewire\Component;
use Filament\Tables\Table;
use App\Models\QuotaTransaction;
use Illuminate\Contracts\View\View;
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
    public User $partner;
    public $total;

    protected InternalPaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = app(InternalPaymentService::class);
    }

    public function table(Table $table): Table
    {

        $this->partner = Auth::user();
        $this->total = QuotaTransaction::where('partner_id', $this->partner->id)->where('payment_status', 'unpaid')->sum('total');

        return $table
            ->query(QuotaTransaction::where('partner_id', $this->partner->id)->where('payment_status', 'unpaid'))
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
            $data = [
                'amount' => $this->total,
                'origin' => 'Quota Debt',
                'partner_id' => $this->partner->id,
            ];

            $result = $this->paymentService->initiatePayment(
                $data
            );

            Notification::make()
                ->title('Paiement initié avec succès')
                ->success()
                ->send();

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
