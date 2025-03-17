<?php

namespace App\Filament\Pages\Admin;

use App\Models\User;

use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Users extends Page implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.admin.users';


    public function table(Table $table): Table
    {
        return $table
            ->query(User::query()->with(['applications']))
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
                TextColumn::make('is_admin'),
                TextColumn::make('applications_count')
                    ->state(function ($record) {
                        return $record->applications()->count();
                    })
            ])
            ->filters([
                // ...
            ])
            ->actions([

                ActionGroup::make([
                    Action::make('update')
                        ->label('Update')
                        ->icon('heroicon-o-pencil-square')
                        ->form([
                            TextInput::make('name')
                                ->required()
                                ->formatStateUsing(fn($record) => $record->name),

                            TextInput::make('email')
                                ->required()
                                ->formatStateUsing(fn($record) => $record->email),

                            CheckBox::make('is_admin')
                                ->required()
                                ->formatStateUsing(function ($record) {
                                    if ($record->is_admin)
                                        return true;
                                    else
                                        return false;
                                }),
                        ])
                        ->action(function ($data, $record) {
                            $record->update([
                                'name' => $data['name'],
                                'email' => $data['email'],
                                'is_admin' => $data['is_admin'],
                            ]);
                        }),

                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->requiresConfirmation()
                        ->disabled(function($record){
                            if($record->is_admin)
                                return true;

                            return false;
                        })
                        ->action(function ($record) {
                            $record->delete();
                        })
                ]),



            ])
            ->headerActions([
                Action::make('new_user')
                    ->label('Create New User')
                    ->form([
                        TextInput::make('name')
                            ->required(),

                        TextInput::make('email')
                            ->email()
                            ->required(),

                        TextInput::make('password'),

                        Checkbox::make('is_admin')
                            ->required()
                            ->default(false),

                    ])
                    ->action(function ($data) {
                        User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => Hash::make($data['password'])
                        ]);
                    })
                    ->requiresConfirmation()

            ])
            ->bulkActions([
                // ...
            ]);
    }
}
