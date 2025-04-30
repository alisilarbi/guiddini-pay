<?php

namespace App\Filament\Pages\Admin;

use Closure;

use App\Models\User;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Actions\Quota\GrantQuota;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Radio;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Toggle;
use App\Actions\Partner\CreatePartner;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;


class Partners extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static string $view = 'filament.pages.admin.partners';

    protected static ?string $navigationGroup = 'Follow-ups';

    public function table(Table $table): Table
    {
        return $table
            ->query(User::where('is_partner', true)->with(['applications', 'partner']))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('applications_count')
                    ->label('Applications Count')
                    ->badge()
                    ->color(function (User $record) {
                        $remaining = $record->remaining_allowance;
                        if ($record->partner_mode === 'quota' && $remaining <= 0) {
                            return 'danger';
                        }
                        return 'success';
                    })
                    ->state(function (User $record) {
                        $count = $record->applications()->count();
                        return $record->partner_mode === 'quota'
                            ? $count . ' (' . $record->remaining_allowance . ' left)'
                            : (string) $count;
                    }),

                TextColumn::make('partner_mode')
                    ->label('Partner Mode')
                    ->badge()
                    ->color(fn(User $record) => $record->partner_mode === 'quota' ? 'warning' : 'success')
                    ->state(function (User $record) {
                        return $record->partner_mode === 'quota'
                            ? 'Quota (' . number_format($record->application_price, 2) . ' DA)'
                            : 'Unlimited';
                    }),
            ])
            ->filters([
                // Future filters
            ])
            ->actions([
                ActionGroup::make([

                    ActionGroup::make([
                        Action::make('update')
                            ->label('Update')
                            ->icon('heroicon-o-pencil-square')
                            ->form([
                                TextInput::make('name')->required(),
                                TextInput::make('email')->required(),
                            ])
                            ->action(fn(array $data, User $record) => $record->update([
                                'name' => $data['name'],
                                'email' => $data['email'],
                            ])),

                        Action::make('updatePassword')
                            ->label('Update Password')
                            ->icon('heroicon-o-lock-closed')
                            ->form([
                                Grid::make(2)->schema([
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->default(fn($record) => $record->email)
                                        ->disabled(),

                                    TextInput::make('new_password')
                                        ->label('New Password')
                                        ->default(fn() => Str::random(12))
                                        ->live()
                                        ->required()
                                        ->suffixActions([
                                            \Filament\Forms\Components\Actions\Action::make('generatePassword')
                                                ->icon('heroicon-o-sparkles')
                                                ->tooltip('Generate New Password')
                                                ->action(fn(Set $set) => $set('new_password', Str::password(12))),
                                        ]),
                                ]),
                            ])
                            ->action(fn(array $data, User $record) => $record->update([
                                'password' => Hash::make($data['new_password']),
                            ])),

                        Action::make('delete')
                            ->label('Delete')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->requiresConfirmation()
                            ->disabled(fn(User $record) => $record->is_admin)
                            ->action(fn(User $record) => $record->delete()),
                    ])->dropdown(false),


                    ActionGroup::make([
                        Action::make('grant_quota')
                            ->label('Grant Quota')
                            ->icon('heroicon-o-plus-circle')
                            ->form([

                                Grid::make(2)->schema([
                                    TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->integer()
                                        ->live()
                                        ->minValue(1)
                                        ->required()
                                        // ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', ''))
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $quantity = (int) $state;
                                            $price = (float) $get('application_price');
                                            $set('amount', number_format((float) $quantity * $price, 2, '.', ''));
                                        }),

                                    TextInput::make('application_price')
                                        ->label('Application Price')
                                        ->numeric()
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                            $price = (float) $state;
                                            $quantity = (int) $get('quantity');
                                            $set('amount', number_format((float) $quantity * $price, 2, '.', ''));
                                        })
                                        ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', ''))
                                        ->default(fn($record) => $record->application_price)
                                        ->disabled(fn($record) => $record->partner_mode === 'unlimited')
                                        ->required()
                                        ->visible(fn(User $record) => $record->partner_mode === 'quota'),
                                ]),

                                TextInput::make('amount')
                                    ->label('Total Amount')
                                    ->live()
                                    ->disabled()
                                    ->suffix('DA')
                                    ->default(0),

                                Toggle::make('is_paid')
                                    ->label('Mark as paid ? ')
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark'),




                            ])
                            ->action(function (array $data, User $record, GrantQuota $grantQuota) {
                                $grantQuota->handle($record, $data);
                                Notification::make()
                                    ->title('Quota Granted')
                                    ->success()
                                    ->send();
                            })
                            ->hidden(fn($record) => $record->partner_mode === 'unlimited'),

                        Action::make('update_application_price')
                            ->label('Update Application Price')
                            ->icon('heroicon-o-cog')
                            ->form([
                                TextInput::make('application_price')
                                    ->label('Application Price')
                                    ->numeric()
                                    ->live()
                                    ->default(fn($record) => $record->application_price)
                                    ->required()
                                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', ''))
                                    ->visible(fn(User $record) => $record->partner_mode === 'quota'),
                            ])
                            ->action(fn(array $data, User $record) => $record->update([
                                'application_price' => $data['application_price'],
                            ]))
                            ->visible(fn(User $record) => $record->partner_mode === 'quota'),

                        Action::make('migrate_to_unlimited')
                            ->label('Migrate to Unlimited')
                            ->icon('heroicon-o-arrow-right')
                            ->form([
                                Toggle::make('default_is_paid')
                                    ->label('Default is Paid')
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark'),
                            ])
                            ->action(fn(User $record) => $record->update([
                                'partner_mode' => 'unlimited',
                                'default_is_paid' => false,
                            ]))
                            ->visible(fn(User $record) => $record->partner_mode === 'quota' && !$record->is_admin && !$record->is_super_admin)
                            ->color('success'),

                        Action::make('migrate_to_quota')
                            ->label('Migrate to Quota')
                            ->icon('heroicon-o-arrow-left')
                            ->form([
                                TextInput::make('application_price')
                                    ->label('Application Price')
                                    ->numeric()
                                    ->default(fn($record) => $record->application_price)
                                    ->required(),
                            ])
                            ->action(fn(User $record, array $data) => $record->update([
                                'partner_mode' => 'quota',
                                'application_price' => $data['application_price'],
                            ]))
                            ->visible(fn(User $record) => $record->partner_mode === 'unlimited' && !$record->is_admin && !$record->is_super_admin)

                    ])
                        ->dropdown(false),
                ]),




            ])
            ->headerActions([


                Action::make('new_partner')
                    ->label('Create New Partner')
                    ->icon('heroicon-o-user-plus')
                    ->steps([
                        Step::make('Information')->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('email')->email()->unique('users')->required(),
                            ]),
                            TextInput::make('password')
                                ->label('Password')
                                ->default(fn() => Str::random(12))
                                ->live()
                                ->required()
                                ->suffixActions([
                                    \Filament\Forms\Components\Actions\Action::make('generatePassword')
                                        ->icon('heroicon-o-sparkles')
                                        ->tooltip('Generate New Password')
                                        ->action(fn(Set $set) => $set('password', Str::password(12))),
                                ]),
                        ]),
                        Step::make('Mode')->schema([
                            Grid::make(2)->schema([
                                Radio::make('partner_mode')
                                    ->label('Partner Mode')
                                    ->live()
                                    ->options([
                                        'quota' => 'Quota',
                                        'unlimited' => 'Unlimited',
                                    ])
                                    ->required(),

                                Toggle::make('default_is_paid')
                                    ->label('Default is Paid')
                                    ->visible(fn($get) => $get('partner_mode') === 'unlimited')
                                    ->onIcon('heroicon-o-check')
                                    ->offIcon('heroicon-o-x-mark'),

                                TextInput::make('application_price')
                                    ->label('Application Price')
                                    ->numeric()
                                    ->default(0)
                                    ->required()
                                    ->visible(fn($get) => $get('partner_mode') === 'quota'),
                            ]),
                        ]),
                    ])
                    ->action(function ($data, CreatePartner $createPartner) {
                        $createPartner->handle($data);
                        Notification::make()
                            ->title('Partner Created')
                            ->success()
                            ->send();
                    }),


            ])
            ->bulkActions([
                // You can add bulk delete or export later
            ]);
    }
}
