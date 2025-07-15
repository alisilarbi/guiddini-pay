<?php

namespace App\Filament\Partner\Pages;

use App\Models\License;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use App\Actions\License\CreateLicense;
use App\Actions\License\UpdateLicense;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Forms\Components\Placeholder;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class SatimLicenses extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $view = 'filament.partner.pages.satim-licenses';
    protected static ?string $navigationParentItem = 'Licenses';
    protected static ?string $navigationGroup = 'Integrations';
    protected static ?int $navigationSort = 5;


    public static function getNavigationBadge(): ?string
    {
        return License::where('partner_id', Auth::user()->id)
            ->where('gateway_type', 'satim')
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                License::where('partner_id', Auth::user()->id)
                    ->where('gateway_type', 'satim')
                    ->with(['user'])
            )
            ->columns([


                Split::make([
                    TextColumn::make('name')
                        ->icon('heroicon-o-identification')
                        ->weight(FontWeight::Bold),

                    Stack::make([
                        TextColumn::make('user.name')
                            ->icon('heroicon-o-user-circle'),
                        TextColumn::make('user.email')
                            ->icon('heroicon-o-envelope'),
                    ]),

                ]),

                Panel::make([

                    Split::make([

                        Stack::make([
                            TextColumn::make('satim_development_username')
                                ->label('Nom d\'utilisateur'),
                            TextColumn::make('satim_development_password')
                                ->label('Mot de passe'),
                            TextColumn::make('satim_development_terminal')
                                ->label('Terminal'),
                        ]),

                        Stack::make([
                            TextColumn::make('satim_production_username')
                                ->label('Nom d\'utilisateur'),
                            TextColumn::make('satim_production_password')
                                ->label('Mot de passe'),
                            TextColumn::make('satim_production_terminal')
                                ->label('Terminal'),
                        ]),

                    ])
                ])->collapsed(true),

            ])
            ->contentGrid([
                'md' => 1,
                'xl' => 2,
            ])
            ->actions([
                Action::make('update')
                    ->label('Modifier')
                    ->fillForm(function ($record) {
                        return [
                            'name' => $record?->name,
                            'satim_development_username' => $record?->satim_development_username,
                            'satim_development_password' => $record?->satim_development_password,
                            'satim_development_terminal' => $record?->satim_development_terminal,
                            'satim_production_username' => $record?->satim_production_username,
                            'satim_production_password' => $record?->satim_production_password,
                            'satim_production_terminal' => $record?->satim_production_terminal,
                        ];
                    })
                    ->form([
                        Fieldset::make('Information')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom de la license')
                                    ->required(),
                            ]),

                        Fieldset::make('Development')
                            ->columns(3)
                            ->schema([
                                TextInput::make('satim_development_username')
                                    ->label('Nom d\'utilisateur')
                                    ->live()
                                    ->required(fn($get) => $get('satim_development_password') || $get('satim_development_terminal')),

                                TextInput::make('satim_development_password')
                                    ->label('Mot de passe')
                                    ->live()
                                    ->required(fn($get) => $get('satim_development_username') || $get('satim_development_terminal')),

                                TextInput::make('satim_development_terminal')
                                    ->label('Terminal')
                                    ->live()
                                    ->required(fn($get) => $get('satim_development_username') || $get('satim_development_password')),
                            ]),

                        Fieldset::make('Production')
                            ->columns(3)
                            ->schema([
                                TextInput::make('satim_production_username')
                                    ->label('Nom d\'utilisateur')
                                    ->live()
                                    ->required(fn($get) => $get('satim_production_password') || $get('satim_production_terminal')),

                                TextInput::make('satim_production_password')
                                    ->label('Mot de passe')
                                    ->live()
                                    ->required(fn($get) => $get('satim_production_username') || $get('satim_production_terminal')),

                                TextInput::make('satim_production_terminal')
                                    ->label('Terminal')
                                    ->live()
                                    ->required(fn($get) => $get('satim_production_username') || $get('satim_production_password')),
                            ])
                    ])
                    ->action(function ($data, License $record, UpdateLicense $updateLicense) {
                        $updateLicense->handle(
                            license: $record,
                            data: $data
                        );

                        Notification::make()
                            ->title('License updated')
                            ->success()
                            ->send();

                        $this->dispatch('refresh-table');
                    }),
            ])
            ->headerActions([


                Action::make('create')
                    ->outlined()
                    ->label('Enregistrer une license')
                    ->form([
                        Fieldset::make('Information')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required(),
                            ]),

                        Fieldset::make('Development')
                            ->columns(3)
                            ->schema([
                                TextInput::make('satim_development_username')
                                    ->label('Nom d\'utilisateur')
                                    ->live()
                                    ->required(function (\Filament\Forms\Get $get) {
                                        $devStarted = filled($get('satim_development_username'))
                                            || filled($get('satim_development_password'))
                                            || filled($get('satim_development_terminal'));

                                        $prodStarted = filled($get('satim_production_username'))
                                            || filled($get('satim_production_password'))
                                            || filled($get('satim_production_terminal'));

                                        return $devStarted || ! $prodStarted;
                                    }),

                                TextInput::make('satim_development_password')
                                    ->label('Mot de passe')
                                    ->live()
                                    ->required(function (\Filament\Forms\Get $get) {
                                        $devStarted = filled($get('satim_development_username'))
                                            || filled($get('satim_development_password'))
                                            || filled($get('satim_development_terminal'));

                                        $prodStarted = filled($get('satim_production_username'))
                                            || filled($get('satim_production_password'))
                                            || filled($get('satim_production_terminal'));

                                        return $devStarted || ! $prodStarted;
                                    }),

                                TextInput::make('satim_development_terminal')
                                    ->label('Terminal')
                                    ->live()
                                    ->required(function (\Filament\Forms\Get $get) {
                                        $devStarted = filled($get('satim_development_username'))
                                            || filled($get('satim_development_password'))
                                            || filled($get('satim_development_terminal'));

                                        $prodStarted = filled($get('satim_production_username'))
                                            || filled($get('satim_production_password'))
                                            || filled($get('satim_production_terminal'));

                                        return $devStarted || ! $prodStarted;
                                    }),
                            ]),

                        Fieldset::make('Production')
                            ->columns(3)
                            ->schema([
                                TextInput::make('satim_production_username')
                                    ->label('Nom d\'utilisateur')
                                    ->live()
                                    ->required(function (\Filament\Forms\Get $get) {
                                        $prodStarted = filled($get('satim_production_username'))
                                            || filled($get('satim_production_password'))
                                            || filled($get('satim_production_terminal'));

                                        $devStarted = filled($get('satim_development_username'))
                                            || filled($get('satim_development_password'))
                                            || filled($get('satim_development_terminal'));

                                        return $prodStarted || ! $devStarted;
                                    }),

                                TextInput::make('satim_production_password')
                                    ->label('Mot de passe')
                                    ->live()
                                    ->required(function (\Filament\Forms\Get $get) {
                                        $prodStarted = filled($get('satim_production_username'))
                                            || filled($get('satim_production_password'))
                                            || filled($get('satim_production_terminal'));

                                        $devStarted = filled($get('satim_development_username'))
                                            || filled($get('satim_development_password'))
                                            || filled($get('satim_development_terminal'));

                                        return $prodStarted || ! $devStarted;
                                    }),

                                TextInput::make('satim_production_terminal')
                                    ->label('Terminal')
                                    ->live()
                                    ->required(function (\Filament\Forms\Get $get) {
                                        $prodStarted = filled($get('satim_production_username'))
                                            || filled($get('satim_production_password'))
                                            || filled($get('satim_production_terminal'));

                                        $devStarted = filled($get('satim_development_username'))
                                            || filled($get('satim_development_password'))
                                            || filled($get('satim_development_terminal'));

                                        return $prodStarted || ! $devStarted;
                                    }),
                            ]),
                    ])
                    ->action(function (array $data, CreateLicense $createLicense) {
                        $data['type'] = 'satim';

                        $createLicense->handle(
                            user: Auth::user(),
                            partner: Auth::user(),
                            data: $data
                        );

                        Notification::make()
                            ->title('License created')
                            ->success()
                            ->send();

                        $this->dispatch('refresh-table');
                    })

            ]);
    }
}
