<?php

namespace App\Filament\User\Pages;

use App\Models\License;
use Filament\Pages\Page;
use Filament\Tables\Table;

use App\Models\Application;
use App\Rules\ValidUrlRule;
use App\Rules\RedirectUrlRule;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\SelectColumn;
use Filament\Infolists\Components\TextEntry;
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
            // ->query(Application::query()->with(['license', 'user']))
            ->query(Application::where('user_id', Auth::user()->id)->with(['license', 'user']))
            ->columns([
                TextColumn::make('name')
                    ->label('App name'),

                TextColumn::make('user.name')
                    ->label('Owner'),

                SelectColumn::make('license_id')
                    ->label('License')
                    ->options(License::where('user_id', Auth::user()->id)->pluck('name', 'id')),

                TextColumn::make('license_name')
                    ->label('License')
                    ->formatStateUsing(function(Application $record){
                        return $record->license->name;
                    })

                // SelectColumn::make('license_env')
                //     ->options(function ($record) {
                //         $license = $record->license;

                //         if (!$license) {
                //             return [];
                //         }

                //         if ($license->satim_production_username && $license->satim_production_password && $license->satim_production_terminal) {
                //             return [
                //                 'development' => 'Development',
                //                 'production' => 'Production',
                //             ];
                //         }

                //         return [
                //             'development' => 'Development',
                //         ];
                //     })
                //     ->rules(['required'])
                //     ->selectablePlaceholder(false)

            ])
            ->actions([
                ActionGroup::make([
                    ActionGroup::make([
                        ViewAction::make('view')
                            ->icon('heroicon-o-eye')
                            ->infolist([

                                Fieldset::make('General Information')
                                    ->schema([

                                        TextEntry::make('name')
                                            ->label('Name'),

                                        TextEntry::make('app_key')
                                            ->label('App Key'),

                                        TextEntry::make('app_secret')
                                            ->label('App Secret'),

                                        TextEntry::make('website_url')
                                            ->label('Website URL'),

                                        TextEntry::make('redirect_url')
                                            ->label('Redirect URL'),


                                    ]),
                            ]),

                        ViewAction::make('view_keys')
                            ->label('Keys')
                            ->icon('heroicon-o-key')
                            ->form([

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('app_key')
                                            ->label('Application Key')
                                            ->formatStateUsing(fn($record) => $record->app_key),

                                        TextInput::make('app_secret')
                                            ->label('Application Secret')
                                            ->formatStateUsing(fn($record) => $record->app_secret)
                                            ->password()
                                            ->revealable(),
                                    ]),

                            ]),

                    ])->dropdown(false),
                ])->tooltip('Actions'),
            ])
            ->headerActions([
            ]);
    }
}
