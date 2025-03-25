<?php

namespace App\Filament\Partner\Pages;

use App\Models\User;
use App\Models\License;
use App\Models\Prospect;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Application;
use Illuminate\Support\Str;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Prospects extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static string $view = 'filament.partner.pages.prospects';

    protected static ?string $navigationGroup = 'CRM';

    public function table(Table $table): Table
    {
        return $table
            ->query(Prospect::where('partner_id', Auth::user()->id))
            ->columns([

                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->color('primary'),

                TextColumn::make('company_name')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function (Prospect $record) {
                        return $record->legal_status . ' ' . $record->company_name;
                    }),

                TextColumn::make('phone')
                    ->label('Phone Number')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable(),

                TextColumn::make('email')
                    ->label('email')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                ViewColumn::make('bank_account')->view('components.tables.columns.bank_account'),

                ViewColumn::make('integrations')->view('components.tables.columns.integrations'),

                TextColumn::make('website_link')
                    ->label('Website')
                    ->limit(30)
                    ->url(fn($record) => $record?->website_link, true)
                    ->hidden(fn($record) => !$record?->website_integration),

                TextColumn::make('programming_languages')
                    ->label('Languages')
                    ->formatStateUsing(fn($state) => implode(', ', json_decode($state ?? '[]')))
                    ->badge()
                    ->color('warning'),

            ])
            ->filters([
                SelectFilter::make('legal_status')
                    ->label('Legal Status')
                    ->options([
                        'EURL' => 'EURL',
                        'SARL' => 'SARL',
                        'SPA' => 'SPA',
                        'SPAS' => 'SPAS',
                        'SPASU' => 'SPASU',
                        'SNC' => 'SNC',
                        'SCS' => 'SCS',
                        'SCA' => 'SCA',
                        'EPIC' => 'EPIC',
                        'GR' => 'GR',
                        'Auto-Entrepreneur' => 'Auto-Entrepreneur',
                        'Association' => 'Association',
                        'Natural-Person' => 'Personne-Physique',
                        'Liberal-Profession' => 'Profession-Libéral',
                    ])
                    ->searchable(),

                TernaryFilter::make('converted')
                    ->label('Converted')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->default(false)
                    ->queries(
                        true: fn(Builder $query) => $query->where('converted', true),
                        false: fn(Builder $query) => $query->where('converted', false),
                    ),
            ])
            ->actions([

                ActionGroup::make([
                    Action::make('convert')
                        ->label('Convertir')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-arrow-path')
                        ->disabled(fn(Prospect $prospect) => $prospect->converted)
                        ->action(function (Prospect $prospect) {
                            $partner = User::with('licenses')->find(Auth::id());
                            $license = $partner->licenses->first();

                            dd($license);

                            $application = Application::create([
                                'name' => $prospect->company_name,
                                'website_url' => $prospect->website_url,
                                'redirect_url' => $prospect->website_url,
                                'partner_id' => $partner->id,
                                'license_id' => $license?->id,
                                'license_env' => 'development',
                            ]);

                            $license = License::firstWhere('name', 'GD01NI');
                            $application->update([
                                'license_env' => 'development',
                                'license_id' => $license->id ?? null,
                            ]);

                            $user = User::where('email', $prospect->email)->first();
                            if (!$user) {
                                $user = User::create([
                                    'name' => $prospect->name,
                                    'email' => $prospect->email,
                                    'password' => Hash::make(Str::random(12)),
                                    'partner_id' => $partner->id,
                                ]);
                            }

                            $application->update([
                                'user_id' => $user->id,
                            ]);

                            $prospect->update([
                                'application_id' => $application->id,
                                'user_id' => $user->id,
                                'converted' => true,
                            ]);
                        }),

                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->action(function (Prospect $prospect) {
                            $prospect->delete();
                        }),

                ])



            ])
            ->headerActions([])
            ->paginated([25, 50, 75, 100, 'all']);;
    }
}
