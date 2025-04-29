<?php

namespace App\Http\Controllers\Api\Partner;

use App\Models\User;
use App\Models\License;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesApiExceptions;
use App\Actions\License\CreateLicense;
use App\Actions\License\UpdateLicense;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\LicenseResource;
use App\Http\Requests\Api\License\StoreLicenseRequest;
use App\Http\Requests\Api\License\UpdateLicenseRequest;

class PartnerLicenseController extends Controller
{
    use HandlesApiExceptions;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $partner = $request->attributes->get('partner');
            $licenses = License::where('partner_id', $partner->id)->get();

            return response()->json([
                'data' => $licenses->isEmpty() ? [] : $licenses->map(fn($license) => [
                    'type' => 'license',
                    'id' => $license->id,
                    'attributes' => $license->toArray()
                ]),
                'meta' => [
                    'code' => $licenses->isEmpty() ? 'NO_LICENSES_FOUND' : 'LICENCES_FOUND',
                    'message' => $licenses->isEmpty() ? 'No licenses available' : 'Licenses retrieved successfully'
                ]
            ], 200);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLicenseRequest $request, CreateLicense $action)
    {
        try {
            $partner = $request->attributes->get('partner');
            $license = $partner->licenses()->first();

            $license = $action->handle(
                user: $partner,
                partner: $partner,
                data: $request->validated()
            );

            return new LicenseResource([
                'success' => true,
                'code' => 'LICENSE_CREATED',
                'message' => 'License savec successfully',
                'data' => $license,
                'http' => 201,
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

            $partner = $request->attributes->get('partner');
            $license = License::where('id', $request->id)
                ->where('partner_id', $partner->id)
                ->firstOrFail();

            return new LicenseResource([
                'success' => true,
                'code' => 'LICENSE_FOUND',
                'message' => 'License retrieved successfully',
                'data' => $license,
                'http' => 200,
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLicenseRequest $request, UpdateLicense $action)
    {
        try {
            $partner = $request->attributes->get('partner');
            $license = License::where('id', $request->id)
                ->where('partner_id', $partner->id)
                ->firstOrFail();

            $license = $action->handle(
                license: $license,
                data: $request->validated()
            );

            return new LicenseResource([
                'success' => true,
                'code' => 'LICENSE_UPDATED',
                'message' => 'License updated successfully',
                'data' => $license,
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

            $partner = $request->attributes->get('partner');
            $license = License::where('id', $request->id)
                ->where('partner_id', $partner->id)
                ->firstOrFail();

            $apps = [];
            if ($license->applications->isNotEmpty()) {
                foreach ($license->applications as $app) {
                    $app->update([
                        'license_id' => null,
                        'license_env' => null,
                    ]);

                    $apps[] = [
                        'type' => 'application',
                        'id' => $app->id,
                        'attributes' => [
                            'name' => $app->name,
                            'license_id' => $app->license_id,
                            'license_env' => $app->license_env,
                        ]
                    ];
                }
            }

            $license->delete();

            return response()->json([
                'data' => $apps ? [
                    'message' => 'Some applications were using this license and have been detached.',
                    'applications' => $apps
                ] : null,
                'meta' => [
                    'code' => 'LICENSE_DELETED',
                    'message' => $apps ? 'License deleted successfully, and applications were detached.' : 'License deleted successfully.'
                ]
            ], 200);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Transfer ownership to another user
     */
    public function transferOwnership(Request $request)
    {
        try {

            $request->validate([
                'license_id' => 'required|string|exists:licenses,id',
                'new_user_id' => 'required|string|exists:users,id'
            ]);

            $partner = $request->attributes->get('partner');

            $license = License::where('id', $request->license_id)
                ->where('partner_id', $partner->id)
                ->firstOrFail();

            $newUser = User::findOrFail($request->new_user_id);

            $license->update([
                'user_id' => $newUser->id,
            ]);

            return new LicenseResource([
                'success' => true,
                'code' => 'OWNERSHIP_TRANSFERRED',
                'message' => 'License ownership transferred successfully',
                'data' => $license,
                'http' => 200,
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }
}
