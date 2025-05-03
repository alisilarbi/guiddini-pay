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
            ->where('is_paid', true)
            ->count();
        $this->unpaidApplications = $this->totalApplications - $this->paidApplications;
        $this->remainingAllowance = $this->partner->remaining_allowance;
        $this->newAllowance = 1;
    }

    public function table(Table $table): Table
    {
        $partnerId = $this->partner->id;

        return $table
            ->query(function () use ($partnerId) {
                // Query for QuotaTransaction events
                $quotaTransactionsSql = QuotaTransaction::select(
                    DB::raw("'QuotaTransaction' as type"),
                    'id',
                    'created_at',
                    'type as action',
                    'is_paid as status',
                    'application_price as cost',
                    'total as impact',
                    'quantity as details',
                    DB::raw('NULL as app_name') // Add placeholder to match Application query
                )->where('partner_id', $partnerId)->toSql();

                // Query for Application creation events, joining with QuotaTransaction
                $applicationsSql = Application::leftJoin('quota_transactions', 'applications.quota_transaction_id', '=', 'quota_transactions.id')
                    ->select(
                        DB::raw("'Application' as type"),
                        'applications.id',
                        'applications.created_at',
                        DB::raw("'Created' as action"),
                        'applications.license_env as status',
                        DB::raw('COALESCE(quota_transactions.application_price, 0) as cost'),
                        DB::raw('COALESCE(quota_transactions.total, 1) as impact'),
                        DB::raw('1 as details'),
                        'applications.name as app_name'
                    )
                    ->where('applications.user_id', $partnerId)
                    ->toSql();

                // Combine the SQL queries with UNION
                $unionSql = "({$quotaTransactionsSql}) UNION ({$applicationsSql})";

                // Use EventHistory with a raw query
                return EventHistory::query()
                    ->from(DB::raw("({$unionSql}) as event_history"))
                    ->setBindings(array_merge(
                        QuotaTransaction::where('partner_id', $partnerId)->getBindings(),
                        Application::where('user_id', $partnerId)->getBindings()
                    ));
            })
            ->columns([
                TextColumn::make('type')
                    ->label('type')
                    ->formatStateUsing(fn($state) => $state === 'QuotaTransaction' ? 'Quota Transaction' : 'Application Creation')
                    ->badge()
                    ->color(fn($state) => $state === 'QuotaTransaction' ? 'info' : 'success')
                    ->sortable(),

                TextColumn::make('action')
                    ->label('action')
                    ->formatStateUsing(function ($record) {
                        if ($record->type === 'QuotaTransaction') {
                            return 'Admin Grant';
                        } else if ($record->type === 'Application') {
                            return $record->app_name ?? 'Unnamed App';
                        }
                        return 'Unknown';
                    })
                    ->badge()
                    ->color(fn($state, $record) => $record->type === 'QuotaTransaction' ? 'info' : 'success')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('status')
                    ->formatStateUsing(function ($record) {
                        if ($record->type === 'QuotaTransaction') {
                            return $record->status ? 'Paid' : 'Unpaid';
                        } else if ($record->type === 'Application') {
                            return $record->is_paid ? 'Paid' : 'Unpaid';
                        }
                        return 'Unknown';
                    })
                    ->badge()
                    ->color(fn($state, $record) => $record->type === 'QuotaTransaction' ? ($state ? 'success' : 'warning') : ($state === 'production' ? 'success' : 'warning'))
                    ->sortable(),

                TextColumn::make('cost')
                    ->label('cost')
                    ->formatStateUsing(function ($record) {
                        return ($record->type === 'QuotaTransaction' ? ($record->impact ?? 0) : ($record->cost ?? 0)) . ' DZD';
                    })
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('details')
                    ->label('details')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->type === 'QuotaTransaction') {
                            return $state == 1 ? '1 App' : "$state Apps";
                        }
                        return '1 App';
                    })
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('created_at')
                    ->dateTime()
                    ->sortable(),
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

    // public function table(Table $table): Table
    // {
    //     $partnerId = $this->partner->id;

    //     return $table
    //         ->query(function () use ($partnerId) {
    //             $quotaTransactionsSql = QuotaTransaction::select(
    //                 DB::raw("'QuotaTransaction' as type"),
    //                 'id',
    //                 'created_at',
    //                 'type as transaction_type',
    //                 'is_paid',
    //                 'application_price',
    //                 'total',
    //                 DB::raw('NULL as application_name')
    //             )->where('partner_id', $partnerId)->toSql();

    //             $applicationsSql = Application::select(
    //                 DB::raw("'Application' as type"),
    //                 'id',
    //                 'created_at',
    //                 DB::raw('NULL as transaction_type'),
    //                 DB::raw('NULL as is_paid'),
    //                 DB::raw('NULL as application_price'),
    //                 DB::raw('NULL as total'),
    //                 'name as application_name'
    //             )->where('user_id', $partnerId)->toSql();

    //             $unionSql = "({$quotaTransactionsSql}) UNION ({$applicationsSql})";

    //             return EventHistory::query()
    //                 ->from(DB::raw("({$unionSql}) as event_history"))
    //                 ->setBindings(array_merge(
    //                     QuotaTransaction::where('partner_id', $partnerId)->getBindings(),
    //                     Application::where('user_id', $partnerId)->getBindings()
    //                 ));
    //         })
    //         ->columns([
    //             TextColumn::make('type')
    //                 ->label('')
    //                 ->formatStateUsing(fn($state) => $state === 'QuotaTransaction' ? 'Quota Transaction' : 'Application Creation')
    //                 ->badge()
    //                 ->color(fn($state) => $state === 'QuotaTransaction' ? 'info' : 'success')
    //                 ->sortable(),

    //             TextColumn::make('created_at')
    //                 ->label('')
    //                 ->dateTime()
    //                 ->sortable(),

    //             TextColumn::make('transaction_type')
    //                 ->label('')
    //                 ->formatStateUsing(fn($state, $record) => $record->type === 'QuotaTransaction' ? ($state ?? 'Unknown') : ($state ?? 'No Transaction'))
    //                 ->badge()
    //                 ->color('gray')
    //                 ->sortable(),

    //             TextColumn::make('is_paid')
    //                 ->label('')
    //                 ->formatStateUsing(fn($state, $record) => $record->type === 'QuotaTransaction' ? ($state ? 'Paid' : 'Unpaid') : ($state ? 'Paid' : 'Unpaid'))
    //                 ->badge()
    //                 ->color(fn($state, $record) => $state ? 'success' : 'danger')
    //                 ->sortable(),

    //             TextColumn::make('application_price')
    //                 ->label('')
    //                 ->formatStateUsing(fn($state, $record) => $state ? number_format($state, 2) . ' DZD' : ($record->type === 'Application' ? 'Included' : '0.00 DZD'))
    //                 ->badge()
    //                 ->color('primary')
    //                 ->sortable(),

    //             TextColumn::make('total')
    //                 ->label('')
    //                 ->formatStateUsing(fn($state, $record) => $state ? number_format($state, 2) . ' DZD' : ($record->type === 'Application' ? 'Included' : '0.00 DZD'))
    //                 ->badge()
    //                 ->color('primary')
    //                 ->sortable(),

    //             TextColumn::make('applications_count')
    //                 ->label('')
    //                 ->formatStateUsing(fn($state, $record) => $record->type === 'QuotaTransaction' ? "Apps: $state" : ($record->application_name ?? 'Unnamed App'))
    //                 ->badge()
    //                 ->color('gray')
    //                 ->sortable(),


    //         ])
    //         ->defaultSort('created_at', 'desc')
    //         ->filters([
    //             // Add filters if needed
    //         ])
    //         ->actions([
    //             // Add actions if needed
    //         ])
    //         ->bulkActions([
    //             // Add bulk actions if needed
    //         ]);
    // }
}
