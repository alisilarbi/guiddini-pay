<?php

namespace App\Filament\Pages\Admin;

use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Application;
use Filament\Tables\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;


class Applications extends Page implements HasForms, HasTable
{

    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.admin.applications';


    public function table(Table $table): Table
    {
        return $table
            ->query(Application::query()->with(['info', 'user', 'productionRequest']))
            ->columns([
                TextColumn::make('info.name')
                    ->label('App name'),

                TextColumn::make('info.support_email')
                    ->label('App email'),

                TextColumn::make('user.name')
                    ->label('Username'),


                ToggleColumn::make('is_production')
                    ->label('Environment prod')
                    ->disabled(function ($record, $state) {
                        if(!$record->satim_production_username or !$record->satim_production_password or !$record->satim_production_terminal)
                            return true;

                        return false;
                    }),

                TextColumn::make('updated_at')
                    ->dateTime(),

            ])
            ->filters([
                // ...
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make('view')
                        ->infolist([
                            Fieldset::make('General Information')
                                ->schema([
                                    TextEntry::make('info.name')
                                        ->label('Name'),

                                    TextEntry::make('info.support_email')
                                        ->label('Email'),

                                    TextEntry::make('info.industries')
                                        ->label('Industries'),

                                    TextEntry::make('info.privacy_policy_url')
                                        ->label('Privacy Policy URL'),

                                    TextEntry::make('info.terms_of_service_url')
                                        ->label('Terms of Service URL'),
                                ]),

                            Fieldset::make('Gateway Information')
                                ->schema([

                                    TextEntry::make('app_key')
                                        ->label('App Key'),

                                    TextEntry::make('app_secret')
                                        ->label('App Secret'),

                                    TextEntry::make('website_url')
                                        ->label('Website URL'),

                                    TextEntry::make('success_redirect_url')
                                        ->label('Success Redirect URL'),

                                    TextEntry::make('fail_redirect_url')
                                        ->label('Fail Redirect URL'),
                                ]),

                            Fieldset::make('SATIM TEST')
                                ->schema([

                                    TextEntry::make('satim_development_username')
                                        ->label('Username'),

                                    TextEntry::make('satim_development_password')
                                        ->label('Password'),

                                    TextEntry::make('satim_development_terminal')
                                        ->label('Terminal ID'),
                                ]),

                            Fieldset::make('SATIM PROD')
                                ->schema([

                                    TextEntry::make('satim_production_username')
                                        ->label('Username'),

                                    TextEntry::make('satim_production_password')
                                        ->label('Password'),

                                    TextEntry::make('satim_production_terminal')
                                        ->label('Terminal ID'),
                                ]),
                        ]),

                    Action::make('production_info')
                        ->label('Production Info')
                        ->fillForm(function ($record) {
                            return [
                                'satim_production_username' => $record->satim_production_username,
                                'satim_production_password' => $record->satim_production_password,
                                'satim_production_terminal' => $record->satim_production_terminal,
                            ];
                        })
                        ->form([
                            TextInput::make('satim_production_username')
                                ->required(),

                            TextInput::make('satim_production_password')
                                ->required(),

                            TextInput::make('satim_production_terminal')
                                ->required(),
                        ])
                        ->action(function ($data, $record) {
                            $record->update([
                                'satim_production_username' => $data['satim_production_username'],
                                'satim_production_password' => $data['satim_production_password'],
                                'satim_production_terminal' => $data['satim_production_terminal'],
                            ]);

                            Notification::make()
                                ->title('Saved successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->action(function($record){
                            $record->delete();
                        })

                ])->tooltip('Actions'),
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
