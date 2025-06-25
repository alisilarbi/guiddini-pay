<?php

namespace App\Filament\Partner\Pages;

use Closure;
use App\Models\User;
use App\Models\License;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Application;
use App\Rules\ValidUrlRule;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use App\Rules\RedirectUrlRule;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use App\Traits\HandlesWebExceptions;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\FontFamily;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Wizard\Step;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\TextEntry;
use App\Actions\Application\CreateApplication;
use App\Actions\Application\DeleteApplication;
use App\Actions\Application\TransferOwnership;
use App\Actions\Application\UpdateApplication;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Applications extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    use HandlesWebExceptions;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static string $view = 'filament.partner.pages.applications';

    protected static ?string $navigationGroup = 'Integrations';

    public function table(Table $table): Table
    {
        return $table
            ->query(Application::where('partner_id', Auth::user()->id)->with(['license', 'user'])->latest())
            ->columns([
                TextColumn::make('name')
                    ->label('App name'),

                TextColumn::make('user.name')
                    ->label('Owner'),

                SelectColumn::make('license_id')
                    ->label('License')
                    ->options(License::where('user_id', Auth::user()->id)->pluck('name', 'id')),

                SelectColumn::make('license_env')
                    ->options(function ($record) {
                        $license = $record->license;

                        if (!$license) {
                            return [];
                        }

                        if ($license->satim_production_username && $license->satim_production_password && $license->satim_production_terminal) {
                            return [
                                'development' => 'Development',
                                'production' => 'Production',
                            ];
                        }

                        return [
                            'development' => 'Development',
                        ];
                    })
                    ->rules(['required'])
                    ->selectablePlaceholder(false)

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

                    ActionGroup::make([


                        Action::make('edit')
                            ->label('Edit')
                            ->icon('heroicon-o-pencil-square')
                            ->fillForm(function ($record) {
                                return [
                                    'name' => $record->name,
                                    'logo' => basename($record->logo),
                                    'website_url' => $record->website_url,
                                    'redirect_url' => $record->redirect_url,
                                    'license' => $record->license_id,
                                    'license_env' => $record->license_env,
                                ];
                            })
                            ->form([
                                FileUpload::make('logo')
                                    ->previewable(true),

                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required(),

                                        TextInput::make('website_url')
                                            ->label('Lien du site web')
                                            ->required()
                                            ->url()
                                            ->rule(new ValidUrlRule())
                                            ->live(),

                                        TextInput::make('redirect_url')
                                            ->label('Lien de redirection')
                                            ->required()
                                            ->url()
                                            ->rule(fn($get) => $get('website_url') ? new RedirectUrlRule($get('website_url')) : 'nullable')
                                            ->live(),

                                        Select::make('license')
                                            ->live()
                                            ->required()
                                            ->options(License::all()->pluck('name', 'id')),

                                        Select::make('license_env')
                                            ->live()
                                            ->required()
                                            ->options(function (Get $get) {

                                                if (!$get('license')) {
                                                    return [];
                                                }

                                                $license = License::where('id', $get('license'))->first();
                                                if (!$license || $license->satim_production_username || $license->satim_production_password || $license->satim_production_terminal) {
                                                    return collect([
                                                        ['id' => 'development', 'name' => 'Development'],
                                                        ['id' => 'production', 'name' => 'Production'],
                                                    ])->pluck('name', 'id')->toArray();
                                                }

                                                return collect([
                                                    ['id' => 'development', 'name' => 'Development'],
                                                ])->pluck('name', 'id')->toArray();
                                            })
                                    ]),

                            ])
                            ->action(function ($data, $record, UpdateApplication $updateApplication) {
                                $updateApplication->handle(
                                    user: Auth::user(),
                                    application: $record,
                                    data: $data,
                                );

                                Notification::make()
                                    ->title('Application updated')
                                    ->success()
                                    ->send();

                                $this->dispatch('refresh-table');
                            }),

                        Action::make('online_payment')
                            ->label('Online Payment')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn(Application $record): string => route('pay', ['slug' => $record->slug], false))
                            ->openUrlInNewTab()



                    ])
                        ->dropdown(false),

                    ActionGroup::make([

                        Action::make('change_ownership')
                            ->label('Transfer Ownership')
                            ->icon('heroicon-o-arrow-path-rounded-square')
                            ->form([

                                Select::make('user')
                                    ->live()
                                    ->required()
                                    ->options(function () {
                                        return User::where('is_user', true)
                                            ->pluck('name', 'id')
                                            ->mapWithKeys(function ($name, $id) {
                                                $user = User::find($id);
                                                return [$id => "{$name} ({$user->email})"];
                                            })
                                            ->all();
                                    }),
                            ])
                            ->action(function (array $data, Application $record, TransferOwnership $transferOwnership) {

                                $transferOwnership->handle(
                                    newOwner: User::where('id', $data['user'])->first(),
                                    application: $record,
                                );

                                Notification::make()
                                    ->title('Ownership transferred')
                                    ->success()
                                    ->send();
                                $this->dispatch('refresh-table');
                            }),

                        Action::make('recover')
                            ->label('Recover')
                            ->icon('heroicon-o-arrow-uturn-left')
                            ->requiresConfirmation()
                            ->action(function (Application $record, TransferOwnership $transferOwnership) {

                                $transferOwnership->handle(
                                    newOwner: Auth::user(),
                                    application: $record,
                                );

                                // Notification::make()
                                //     ->title('Ownership recovered')
                                //     ->success()
                                //     ->send();

                                $this->dispatch('refresh-table');

                            })
                            ->disabled(function (Application $record) {
                                if ($record->user_id === Auth::user()->id)
                                    return true;

                                return false;
                            }),

                    ])->dropdown(false),

                    ActionGroup::make([

                        Action::make('delete')
                            ->label('Delete')
                            ->color('danger')
                            ->icon('heroicon-o-x-circle')
                            ->requiresConfirmation()
                            ->action(function ($record, DeleteApplication $deleteApplication) {
                                $deleteApplication->handle(
                                    application: $record,
                                );
                            })

                    ])->dropdown(false),

                ])->tooltip('Actions'),
            ])
            ->headerActions([
                Action::make('create')
                    ->steps([
                        Step::make('Information Général')
                            ->schema([
                                TextInput::make('name')
                                    ->required(),

                                FileUpload::make('logo')
                                    ->image(),
                            ]),
                        Step::make('Fonctionnement')
                            ->schema([

                                TextInput::make('website_url')
                                    ->label('Lien du site web')
                                    ->required()
                                    ->url()
                                    ->rule(new ValidUrlRule())
                                    ->live(),

                                TextInput::make('redirect_url')
                                    ->label('Lien de redirection')
                                    ->required()
                                    ->url()
                                    ->rule(fn($get) => $get('website_url') ? new RedirectUrlRule($get('website_url')) : 'nullable')
                                    ->live()
                            ]),

                        Step::make('env')
                            ->label('License')
                            ->schema([
                                Select::make('license')
                                    ->live()
                                    ->required()
                                    ->options(License::where('user_id', Auth::user()->id)->pluck('name', 'id')),

                                Select::make('license_env')
                                    ->live()
                                    ->required()
                                    ->options(function (Get $get) {

                                        if (!$get('license')) {
                                            return [];
                                        }

                                        $license = License::where('id', $get('license'))->first();
                                        if (!$license || $license->satim_production_username || $license->satim_production_password || $license->satim_production_terminal) {
                                            return collect([
                                                ['id' => 'development', 'name' => 'Development'],
                                                ['id' => 'production', 'name' => 'Production'],
                                            ])->pluck('name', 'id')->toArray();
                                        }

                                        return collect([
                                            ['id' => 'development', 'name' => 'Development'],
                                        ])->pluck('name', 'id')->toArray();
                                    })

                            ]),


                    ])
                    ->action(function (array $data, CreateApplication $createApplication) {

                        try {
                            $createApplication->handle(
                                user: Auth::user(),
                                partner: Auth::user(),
                                data: $data,
                            );

                            Notification::make()
                                ->title('Application created')
                                ->success()
                                ->send();

                            $this->dispatch('refresh-table');

                        } catch (\Throwable $e) {
                            $this->handleWebException($e);
                        }
                    })
                    ->disabled(function(){
                        $partner = User::where('id', Auth::user()->id)->first();

                        if($partner->canCreateApplication()){
                            return false;
                        }
                        return true;
                    }),
            ]);
    }
}
