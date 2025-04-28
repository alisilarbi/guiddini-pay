<?php

namespace App\Filament\Partner\Pages;

use Closure;
use App\Models\User;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use App\Actions\Client\CreateClient;
use App\Actions\Client\UpdateClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Clients extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static string $view = 'filament.partner.pages.clients';

    protected static ?string $navigationGroup = 'CRM';

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->with(['applications', 'partner'])->where('partner_id', Auth::user()->id))
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('partner.name')
                    ->state(function (User $record) {
                        $user = User::where('id', $record->partner_id)->first();
                        return $user->name;
                    }),
                TextColumn::make('applications_count')
                    ->state(function (User $record) {
                        return $record->applications()->count();
                    })
            ])
            ->filters([
                // ...
            ])
            ->actions([

                ActionGroup::make([
                    Action::make('update')
                        ->label('Update')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                            TextInput::make('name')
                                ->required()
                                ->formatStateUsing(fn($record) => $record->name),

                            TextInput::make('email')
                                ->required()
                                ->formatStateUsing(fn($record) => $record->email),

                        ])
                        ->action(function ($data, $record, UpdateClient $updateClient) {

                            $updateClient->handle(
                                client: $record,
                                data: $data
                            );

                            Notification::make()
                                ->title('Client updated')
                                ->success()
                                ->send();

                            $this->dispatch('refresh-table');
                        }),

                    Action::make('updatePassword')
                        ->label('Update Password')
                        ->icon('heroicon-o-lock-closed')
                        ->form([
                            Grid::make(2)
                                ->schema([
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
                                            // Generate password button
                                            \Filament\Forms\Components\Actions\Action::make('generatePassword')
                                                ->icon('heroicon-o-sparkles')
                                                ->tooltip('Generate New Password')
                                                ->action(function (Set $set) {
                                                    $set('new_password', Str::password(12));
                                                }),

                                        ]),
                                ])

                        ])
                        ->action(function ($data, $record, UpdateClient $updateClient) {
                            $updateClient->handle(
                                client: $record,
                                data: $data
                            );

                            Notification::make()
                                ->title('Client password updated')
                                ->success()
                                ->send();

                            $this->dispatch('refresh-table');
                        }),

                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->disabled(function ($record) {
                            if ($record->is_admin)
                                return true;

                            return true;
                        })
                        ->action(function ($record) {
                            $record->delete();
                        }),



                ]),

            ])
            ->headerActions([

                Action::make('new_client')
                    ->label('Create New Client')
                    ->form([
                        TextInput::make('name')
                            ->required(),

                        TextInput::make('email')
                            ->email()
                            ->unique('users')
                            ->required(),

                        TextInput::make('password')
                            ->label('Password')
                            ->default(fn() => Str::random(12))
                            ->live()
                            ->required()
                            ->suffixActions([
                                \Filament\Forms\Components\Actions\Action::make('generatePassword')
                                    ->icon('heroicon-o-sparkles')
                                    ->tooltip('Generate New Password')
                                    ->action(function (Set $set) {
                                        $set('password', Str::password(12));
                                    }),

                            ]),

                    ])
                    ->action(function ($data, CreateClient $createClient) {
                        $createClient->handle(
                            partner: Auth::user(),
                            data: $data
                        );

                        Notification::make()
                            ->title('Client created')
                            ->success()
                            ->send();

                        $this->dispatch('refresh-table');
                    })
                    ->requiresConfirmation(),

            ])
            ->bulkActions([
                // ...
            ]);
    }
}
