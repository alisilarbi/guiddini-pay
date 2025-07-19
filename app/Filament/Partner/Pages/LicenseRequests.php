<?php

namespace App\Filament\Partner\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use App\Models\Application;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Concerns\InteractsWithForms;

class LicenseRequests extends Page implements HasForms
{
    use InteractsWithForms;


    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.partner.pages.license-requests';

    protected static ?string $navigationGroup = 'Certifications';

    protected static ?int $navigationSort = 9;

    public ?array $data = [];
    public $name;
    public $application_id;
    public $bank_document;
    public $registration_document;

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                Split::make([

                    Grid::make(1)
                        ->schema([
                            Grid::make(2)
                                ->schema([

                                    Section::make('Identification')
                                        ->schema([

                                            Grid::make(2)
                                                ->schema([

                                                    TextInput::make('name')
                                                        ->required()
                                                        ->label('Nom de la license'),

                                                    Select::make('application_id')
                                                        ->required()
                                                        ->label('Pour quel application ?')
                                                        // ->searchable()
                                                        // ->native(false)
                                                        ->placeholder('Sélectionner une application')
                                                        ->options(Application::where('partner_id', Auth::user()->id)->pluck('name', 'id'))
                                                ])

                                        ]),

                                ]),

                            Section::make('Document d\'enregistrement')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([

                                            FileUpload::make('bank_document')
                                                ->required()
                                                ->label('Relevé bancaire ou équivalent')
                                                ->acceptedFileTypes(['application/pdf', 'image/*'])
                                                ->maxSize(10240), // 10 MB,

                                            FileUpload::make('registration_document')
                                                ->required()
                                                ->label('Registre de commerce ou équivalent')
                                                ->acceptedFileTypes(['application/pdf', 'image/*'])
                                                ->maxSize(10240), // 10 MB
                                        ]),
                                ]),
                            ViewField::make('submit_button')
                                ->view('components.forms.license-request-submit-button') // you create this blade file
                                ->columnSpanFull(),
                        ]),

                    ViewField::make('alert')
                        ->view('components.forms.license-request-alert')
                        ->grow(true)

                ])->from('md')


            ])
            ->statePath('data');
    }

    public function create()
    {
        $this->validate();

        $data = $this->data;

        $data['partner_id'] = Auth::user()->id;




        $this->dispatch('success', 'License request created successfully.');

        $this->reset('data');
    }
}
