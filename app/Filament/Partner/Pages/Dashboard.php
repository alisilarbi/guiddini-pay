<?php

namespace App\Filament\Partner\Pages;

use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;



class Dashboard extends \Filament\Pages\Dashboard implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.partner.pages.dashboard';
    protected static ?int $navigationSort = 1;

    public function mount()
    {
        $user = Auth::user();

        if ($user->is_partner) {
            if (!$user->partner_key or !$user->partner_secret) {
                $user->update([
                    'partner_key' => 'APP-' . strtoupper(Str::random(18)),
                    'partner_secret' => 'SEC-' . Str::random(32)
                ]);
            }

            $this->form->fill([
                'partner_key' => $user->partner_key,
                'partner_secret' => $user->partner_secret,
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Clés API des partenaires')
                    ->description('Utilisez ces clés APi pour consommer les endpoints des partenaires et garantir une personnalisation complète de votre compte et vos intégrations.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('partner_key')
                                    ->password()
                                    ->disabled()
                                    ->revealable(),
                                TextInput::make('partner_secret')
                                    ->password()
                                    ->disabled()
                                    ->revealable()
                            ]),
                    ])
                    ->headerActions([
                        Action::make('create')
                            ->outlined()
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->label('Documentation')
                            ->url('https://www.guiddini.dz/docs')
                            ->openUrlInNewTab()


                    ])
            ])

            ->statePath('data');
    }
}
