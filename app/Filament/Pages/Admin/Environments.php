<?php

namespace App\Filament\Pages\Admin;

use Filament\Forms\Get;

use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Environment;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Environments extends Page implements HasForms, HasTable
{

    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.admin.environments';

    public function table(Table $table): Table
    {
        return $table
            ->query(Environment::query()->with(['user']))
            ->columns([

                Split::make([

                    Stack::make([
                        TextColumn::make('name')
                            ->weight(FontWeight::Bold),
                    ]),

                    Stack::make([
                        TextColumn::make('satim_development_username'),
                        TextColumn::make('satim_development_password'),
                        TextColumn::make('satim_development_terminal'),
                    ]),

                    Stack::make([
                        TextColumn::make('satim_production_username'),
                        TextColumn::make('satim_production_password'),
                        TextColumn::make('satim_production_terminal'),
                    ]),
                ])

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
                            ->columns(3)
                            ->schema([
                                TextInput::make('satim_development_username')
                                    ->live()
                                    ->required(),
                                TextInput::make('satim_development_password')
                                    ->live()
                                    ->required(),
                                TextInput::make('satim_development_terminal')
                                    ->live()
                                    ->required(),
                            ]),

                        Fieldset::make('Production')
                            ->columns(3)
                            ->schema([
                                TextInput::make('satim_production_username')
                                    ->live(),
                                TextInput::make('satim_production_password')
                                    ->live(),
                                TextInput::make('satim_production_terminal')
                                    ->live(),
                            ])
                    ])
                    ->action(function ($data) {
                        Environment::create([
                            'user_id' => Auth::user()->id,

                            'name' => $data['name'],
                            'type' => $data['type'],

                            'satim_development_username' => $data['satim_development_username'],
                            'satim_development_password' => $data['satim_development_password'],
                            'satim_development_terminal' => $data['satim_development_terminal'],

                            'satim_production_username' => $data['satim_production_username'],
                            'satim_production_password' => $data['satim_production_password'],
                            'satim_prodution_terminal' => $data['satim_production_terminal'],
                        ]);
                    })
            ]);
    }
}
