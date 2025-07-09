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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class PosteDzLicenses extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $view = 'filament.partner.pages.poste-dz-licenses';
    protected static ?string $navigationParentItem = 'Licenses';
    protected static ?string $navigationGroup = 'Integrations';
    protected static ?int $navigationSort = 6;

    public static function getNavigationBadge(): ?string
    {
        return License::where('partner_id', Auth::user()->id)
            ->where('gateway_type', 'poste_dz')
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                License::where('partner_id', Auth::user()->id)
                    ->where('gateway_type', 'poste_dz')
                    ->with(['user'])
            )
            ->striped()
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
                            TextColumn::make('poste_dz_development_username'),
                            TextColumn::make('poste_dz_development_password'),
                        ]),
                        Stack::make([
                            TextColumn::make('poste_dz_production_username'),
                            TextColumn::make('poste_dz_production_password'),
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
                    ->fillForm(function ($record) {
                        return [
                            'name' => $record?->name,
                            'poste_dz_development_username' => $record?->poste_dz_development_username,
                            'poste_dz_development_password' => $record?->poste_dz_development_password,
                            'poste_dz_production_username' => $record?->poste_dz_production_username,
                            'poste_dz_production_password' => $record?->poste_dz_production_password,
                        ];
                    })
                    ->form([
                        Fieldset::make('Information')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required(),
                            ]),

                        Fieldset::make('Development')
                            ->columns(2)
                            ->schema([
                                TextInput::make('poste_dz_development_username')
                                    ->live()
                                    ->required(fn($get) => $get('poste_dz_development_password')),
                                TextInput::make('poste_dz_development_password')
                                    ->live()
                                    ->required(fn($get) => $get('poste_dz_development_username')),
                            ]),

                        Fieldset::make('Production')
                            ->columns(2)
                            ->schema([
                                TextInput::make('poste_dz_production_username')
                                    ->live()
                                    ->required(fn($get) => $get('poste_dz_production_password')),

                                TextInput::make('poste_dz_production_password')
                                    ->live()
                                    ->required(fn($get) => $get('poste_dz_production_username')),
                            ])
                    ])
                    ->action(function ($data, License $record, UpdateLicense $updateLicense) {
                        $data['type'] = 'poste_dz';

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
                    ->label('Create')
                    ->form([

                        Fieldset::make('Information')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required(),
                            ]),

                        Fieldset::make('Development')
                            ->columns(2)
                            ->schema([
                                TextInput::make('poste_dz_development_username')
                                    ->live()
                                    ->required(fn($get) => $get('poste_dz_development_password')),
                                TextInput::make('poste_dz_development_password')
                                    ->live()
                                    ->required(fn($get) => $get('poste_dz_development_username')),

                            ]),

                        Fieldset::make('Production')
                            ->columns(2)
                            ->schema([
                                TextInput::make('poste_dz_production_username')
                                    ->live()
                                    ->required(fn($get) => $get('poste_dz_production_password')),
                                TextInput::make('poste_dz_production_password')
                                    ->live()
                                    ->required(fn($get) => $get('poste_dz_production_username')),

                            ])
                    ])
                    ->action(function ($data, CreateLicense $createLicense) {
                        $data['gateway_type'] = 'poste_dz';
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
