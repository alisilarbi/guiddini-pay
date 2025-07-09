<?php

namespace App\Filament\Partner\Pages;

use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
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
                Section::make('Rate limiting')
                    ->description('Prevent abuse by limiting the number of requests per period')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('partner_key')
                                    ->disabled(),
                                TextInput::make('partner_secret')
                                    ->disabled()
                            ]),
                    ])
            ])
            ->statePath('data');
    }
}
