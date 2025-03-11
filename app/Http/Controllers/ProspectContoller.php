<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use Illuminate\Http\Request;
use App\Traits\HandlesApiExceptions;
use App\Http\Resources\StandardResponse;

class ProspectContoller extends Controller
{
    use HandlesApiExceptions;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'nullable|string',
            'company_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'legal_status' => 'nullable|string',
            'has_bank_account' => 'boolean',
            'bank_name' => 'nullable|string',
            'website_integration' => 'boolean',
            'mobile_integration' => 'boolean',
            'website_link' => 'nullable|string',
            'programming_languages' => 'nullable|json',
        ]);

        try {

            $prospect = Prospect::create([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'phone' => $request->phone,
                'email' => $request->phone,
                'legal_status' => $request->legal_status,
                'has_bank_account' => $request->has_bank_account,
                'bank_name' => $request->bank_name,
                'website_integration' => $request->website_integration,
                'mobile_integration' => $request->mobile_integration,
                'website_link' => $request->website_link,
                'programming_languages' => $request->programming_languages,
            ]);

            return new ProspectApiResource([
                'success' => true,
                'code' => 'PROSPECT_CREATED',
                'message' => 'Inquiry sent successfully',
                'data' => $prospect,
                'http_code' => 201,
            ]);

        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Prospect $prospect)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prospect $prospect)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prospect $prospect)
    {
        //
    }
}
