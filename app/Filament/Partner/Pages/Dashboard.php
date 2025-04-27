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

    public function mount()
    {
        $user = Auth::user();

        if(!$user->app_key OR !$user->app_secret)
        {
            $user->update([
                'user_key' => 'APP-' . strtoupper(Str::random(18)),
                'user_secret' => 'SEC-' . Str::random(32)
            ]);
        }

        $this->form->fill([
            'user_key' => $user->user_key,
            'user_secret' => $user->user_secret,
        ]);
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
                                TextInput::make('user_key')
                                    ->disabled(),
                                TextInput::make('user_secret')
                                    ->disabled()
                            ]),
                    ])
            ])
            ->statePath('data');
    }


}
