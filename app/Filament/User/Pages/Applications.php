<?php

namespace App\Filament\User\Pages;

use Filament\Forms\Form;

use Filament\Pages\Page;
use Filament\Tables\Table;

use App\Models\Application;
use Illuminate\Support\Str;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Applications extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.user.pages.applications';

    public function table(Table $table): Table
    {
        return $table
            ->query(Application::query())
            ->columns([
                TextColumn::make('name'),

                TextColumn::make('app_key')
                    ->copyable()
                    ->copyMessage('API Key copied'),

                TextColumn::make('updated_at')
                    ->dateTime(),

            ])
            ->filters([
                // ...
            ])
            ->actions([
                ViewAction::make('view')
                    ->form([
                        TextInput::make('name')
                            ->disabled(),
                        TextInput::make('app_key')
                            ->disabled(),
                        TextInput::make('secret_key')
                            ->disabled(),
                    ])

            ])
            ->headerActions([
                Action::make('create')
                    ->form([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('app_key')
                            ->readOnly()
                            ->default(Str::random(40))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('secret_key')
                            ->password()
                            ->readOnly()
                            ->default(Str::random(40))
                            ->maxLength(255),
                    ])
                    ->action(function (array $data) {

                        Application::create([
                            'name' => $data['name'],
                            'app_key' => $data['app_key'],
                            'secret_key' => $data['secret_key'],
                            'user_id' => Auth::user()->id
                        ]);
                    })
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
