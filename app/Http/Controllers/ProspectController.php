<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Prospect;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\HandlesApiExceptions;
use App\Http\Resources\ProspectApiResource;
use App\Http\Resources\API\ProspectResource;

class ProspectController extends Controller
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
        try {
            $request->validate([
                'name' => 'required|string',
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
                'needs_help' => 'nullable|boolean',
            ]);

            $prospect = Prospect::create([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'legal_status' => $request->legal_status,
                'has_bank_account' => $request->has_bank_account,
                'bank_name' => $request->bank_name,
                'website_integration' => $request->website_integration,
                'mobile_integration' => $request->mobile_integration,
                'website_link' => $request->website_link,
                'programming_languages' => $request->programming_languages,
                'reference' => strtoupper(Str::random(2)) . rand(10, 99),
                'needs_help' => $request->needs_help,
            ]);

            return new ProspectResource([
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
    public function show(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required|string',
            ]);

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $prospect = Prospect::where('id', $request->id)
                ->firstOrFail();

            return new ProspectResource([
                'success' => true,
                'code' => 'PROSPECT_FOUND',
                'message' => 'Application retrieved successfully',
                'data' => $prospect,
                'http' => 200,
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|string',
                'name' => 'sometimes|required|string',
                'company_name' => 'sometimes|required|string',
                'phone' => 'sometimes|required|string',
                'email' => 'sometimes|required|string',
                'legal_status' => 'sometimes|required|string',
                'has_bank_account' => 'sometimes|required|boolean',
                'bank_name' => 'sometimes|required|string',
                'converted' => 'sometimes|required|boolean',
                'website_integration' => 'sometimes|required|boolean',
                'mobile_integration' => 'sometimes|required|boolean',
                'needs_help' => 'sometimes|required|string',
                'reference' => 'sometimes|required|string',
                'website_link' => 'sometimes|required|string',
                'programming_languages' => 'sometimes|required|json',
            ]);

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $prospect = Prospect::where('id', $request->id)
                ->firstOrFail();

            $prospect->update($request->only([
                'name',
                'company_name',
                'phone',
                'email',
                'legal_status',
                'has_bank_account',
                'bank_name',
                'converted',
                'website_integration',
                'mobile_integration',
                'needs_help',
                'reference',
                'website_link',
                'programming_languages'
            ]));

            return new ProspectResource([
                'success' => true,
                'code' => 'PROSPECT_UPDATED',
                'message' => 'Prospect updated successfully',
                'data' => $prospect,
                'http' => 200,
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
    }
}
