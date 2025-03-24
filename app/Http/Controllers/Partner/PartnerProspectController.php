<?php

namespace App\Http\Controllers\Partner;

use App\Models\User;
use App\Models\Prospect;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesApiExceptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\API\ClientResource;
use App\Http\Resources\ProspectApiResource;
use App\Http\Resources\API\ProspectResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PartnerProspectController extends Controller
{
    use HandlesApiExceptions;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $prospects = Prospect::where('converted', false)
                ->where('partner_id', $user->id)
                ->get();

            return response()->json([
                'data' => $prospects->isEmpty() ? [] : $prospects->map(fn($prospect) => [
                    'type' => 'prospect',
                    'id' => $prospect->id,
                    'attributes' => $prospect->toArray()
                ]),
                'meta' => [
                    'code' => $prospects->isEmpty() ? 'NO_PROSPECTS_FOUND' : 'PROSPECTS_FOUND',
                    'message' => $prospects->isEmpty() ? 'No prospects found' : 'Prospects retrieved successfully'
                ]
            ], 200);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
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
                'website_url' => 'nullable|string',
                'programming_languages' => 'nullable|json',
                'needs_help' => 'nullable|boolean',
            ]);

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }


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
                'website_url' => $request->website_url,
                'programming_languages' => $request->programming_languages,
                'reference' => strtoupper(Str::random(2)) . rand(10, 99),
                'needs_help' => $request->needs_help,
                'converted' => false,
                'partner_id' => $user->id,
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
                ->where('converted', false)
                ->where('partner_id', $user->id)
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
                'website_url' => 'sometimes|required|string',
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
                ->where('partner_id', $user->id)
                ->where('converted', false)
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
                'website_url',
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
                ->where('partner_id', $user->id)
                ->where('converted', false)
                ->firstOrFail();

            if ($prospect->converted) {
                throw new \Exception('PROSPECT_CONVERTED');
            }

            $prospect->delete();

            return response()->json([
                'data' => null,
                'meta' => [
                    'code' => 'PROSPECT_DELETED',
                    'message' => 'Prospect deleted successfully',
                ]
            ], 200);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    public function convert(Request $request)
    {

        $request->validate([
            'id' => 'required|string',
        ]);

        $appKey = $request->header('x-app-key');
        $secretKey = $request->header('x-secret-key');

        $partner = User::where('app_key', $appKey)
            ->where('app_secret', $secretKey)
            ->first();

        if (!$partner) {
            throw new \Exception('Unauthorized', 401);
        }

        $prospect = Prospect::where('id', $request->id)
            ->where('partner_id', $partner->id)
            ->firstOrFail();

        $user = User::where('email', $prospect->email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $prospect->name,
                'email' => $prospect->email,
                'password' => Hash::make(Str::random(12)),
                'partner_id' => $partner->id,
            ]);
        }

        $application = Application::create([
            'name' => $prospect->name,
            'website_url' => $prospect->website_url,
            'redirect_url' => $prospect->website_url,
            'user_id' => $user->id,
            'partner_id' => $partner->id,
            'license_id' => $partner->licenses()->first()->id,
            'license_env' => 'development',
        ]);

        return new ClientResource([
            'success' => true,
            'code' => 'PROSPECT_CONVERTED',
            'message' => 'Converted to client successfully',
            'data' => $user,
            'http' => 201,
        ]);


        try {

        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }
}
