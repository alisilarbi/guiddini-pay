<?php

namespace App\Filament\User\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
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

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.user.pages.dashboard';

    // public function mount()
    // {
        // $user = Auth::user();

        // if (!$user->app_key or !$user->app_secret) {
        //     $user->update([
        //         'app_key' => 'APP-' . strtoupper(Str::random(18)),
        //         'app_secret' => 'SEC-' . Str::random(32)
        //     ]);
        // }

        // $this->form->fill([
        //     'app_key' => $user->app_key,
        //     'app_secret' => $user->app_secret,
        // ]);
    // }

    // public function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Section::make('Rate limiting')
    //                 ->description('Prevent abuse by limiting the number of requests per period')
    //                 ->schema([
    //                     Grid::make(2)
    //                         ->schema([
    //                             TextInput::make('app_key')
    //                                 ->disabled(),
    //                             TextInput::make('app_secret')
    //                                 ->disabled()
    //                         ]),
    //                 ])
    //         ])
    //         ->statePath('data');
    // }
}
