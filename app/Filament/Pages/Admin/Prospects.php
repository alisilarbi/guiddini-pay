<?php

namespace App\Filament\Pages\Admin;

use App\Models\User;
use App\Models\License;
use App\Models\Prospect;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Application;
use Illuminate\Support\Str;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Prospects extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.admin.prospects';

    public function table(Table $table): Table
    {
        return $table
            ->query(Prospect::query())
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
                    ->icon('heroicon-m-phone')
                    ->copyable(),

                // IconColumn::make('has_bank_account')
                //     ->label('Bank Account')
                //     ->boolean()
                //     ->sortable()
                //     ->trueIcon('heroicon-o-check-circle')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->trueColor('success')
                //     ->falseColor('danger'),


                // IconColumn::make('website_integration')
                //     ->label('Website Int.')
                //     ->boolean()
                //     ->sortable()
                //     ->trueIcon('heroicon-o-globe-alt')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->trueColor('success')
                //     ->falseColor('gray'),

                // IconColumn::make('mobile_integration')
                //     ->label('Mobile Int.')
                //     ->boolean()
                //     ->sortable()
                //     ->trueIcon('heroicon-o-device-phone-mobile')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->trueColor('success')
                //     ->falseColor('gray'),

                // TextColumn::make('integrations')
                //     ->label('Integrations')
                //     ->formatStateUsing(fn($record) => view('components.tables.columns.integrations', ['record' => $record])),

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
                Action::make('convert')
                    ->label('Convertir')
                    // ->requiresConfirmation()
                    ->action(function (Prospect $prospect) {

                        $application = Application::create([
                            'name' => $prospect->company_name,
                            'website_url' => $prospect->website_link,
                            'redirect_url' => $prospect->website_link,
                        ]);

                        $license = License::firstWhere('name', 'GD01NI');

                        $application->update([
                            'license_env' => 'development',
                            'license_id' => $license->id ?? null,
                        ]);

                        $user = User::create([
                            'name' => $prospect->name,
                            // 'email' => $prospect->email,
                            'email' => 'fake_' . Str::random(8) . '@example.com',
                            'password' => Hash::make(Str::random(12)),
                        ]);

                        $prospect->update([
                            'application_id' => $application->id,
                            'user_id' => $user->id,
                            'converted' => true,
                        ]);
                    })

            ])
            ->headerActions([]);
    }
}
