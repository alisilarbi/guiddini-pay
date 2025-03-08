<?php

namespace App\Filament\Pages\Admin;

use Closure;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
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
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;


class Applications extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.admin.applications';

    public function mount(): void
    {
        $license = License::first();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Application::query()->with(['license', 'user']))
            ->columns([
                TextColumn::make('name')
                    ->label('App name'),

                TextColumn::make('user.name')
                    ->label('Owner'),

                SelectColumn::make('license_id')
                    ->label('License')
                    ->options(License::all()->pluck('name', 'id')),

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

                                    TextEntry::make('success_redirect_url')
                                        ->label('Success Redirect URL'),

                                    TextEntry::make('fail_redirect_url')
                                        ->label('Fail Redirect URL'),
                                ]),

                            Fieldset::make('SATIM TEST')
                                ->schema([

                                    TextEntry::make('license.satim_development_username')
                                        ->label('Username'),

                                    TextEntry::make('license.satim_development_password')
                                        ->label('Password'),

                                    TextEntry::make('license.satim_development_terminal')
                                        ->label('Terminal ID'),
                                ]),

                            Fieldset::make('SATIM PROD')
                                ->schema([

                                    TextEntry::make('license.satim_production_username')
                                        ->label('Username'),

                                    TextEntry::make('license.satim_production_password')
                                        ->label('Password'),

                                    TextEntry::make('license.satim_production_terminal')
                                        ->label('Terminal ID'),
                                ]),
                        ]),

                    // Action::make('edit')
                    //     ->label('Edit')
                    //     ->

                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($record) {
                            $record->delete();
                        })

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
                                    ->rule(fn ($get) => $get('website_url') ? new RedirectUrlRule($get('website_url')) : 'nullable')
                                    ->live()
                            ]),

                        Step::make('env')
                            ->label('License')
                            ->schema([
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
                    ->action(function (array $data) {

                        $application = Application::create([
                            'name' => $data['name'],
                            'website_url' => $data['website_url'],
                            'redirect_url' => $data['redirect_url'],
                        ]);

                        if ($data['logo']) {
                            $tempPath = Storage::disk('public')->path($data['logo']);
                            $newFileName = Str::random(40) . '.' . pathinfo($tempPath, PATHINFO_EXTENSION);
                            $destination = 'applications/' . $application->id;

                            Storage::disk('private')->putFileAs($destination, $tempPath, $newFileName);
                            Storage::disk('public')->delete($tempPath);

                            $path = $destination . '/' . $newFileName;
                            $application->update([
                                'logo' => $path,
                            ]);
                        }

                        $env = License::where('id', $data['license'])->first();
                        $application->update([
                            'license_env' => $data['license_env'],
                            'license_id' => $env->id,
                        ]);
                    }),


            ]);
    }
}
