<?php

namespace App\Http\Controllers\Api\Partner;

use App\Models\User;
use App\Models\License;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesApiExceptions;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\LicenseResource;

class PartnerLicenseController extends Controller
{
    use HandlesApiExceptions;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $partnerKey = $request->header('x-partner-key');
            $partnerSecret = $request->header('x-partner-secret');

            $user = User::where('partner_key', $partnerKey)
                ->where('partner_secret', $partnerSecret)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $licenses = License::where('user_id', $user->id)->get();

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
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'satim_development_username' => 'nullable|string',
                'satim_development_password' => 'nullable|string',
                'satim_development_terminal' => 'nullable|string',
                'satim_production_username' => 'nullable|string',
                'satim_production_password' => 'nullable|string',
                'satim_production_terminal' => 'nullable|string',
            ]);

            $validator->after(function ($validator) use ($request) {
                $devMissing = [];
                if (!$request->filled('satim_development_username')) {
                    $devMissing[] = 'satim_development_username';
                }
                if (!$request->filled('satim_development_password')) {
                    $devMissing[] = 'satim_development_password';
                }
                if (!$request->filled('satim_development_terminal')) {
                    $devMissing[] = 'satim_development_terminal';
                }
                if (count($devMissing) > 0 && count($devMissing) < 3) {
                    $validator->errors()->add('satim_development_credentials', 'All development credentials are required together. You are missing ' . implode(', ', $devMissing) . '.');
                }

                $prodMissing = [];
                if (!$request->filled('satim_production_username')) {
                    $prodMissing[] = 'satim_production_username';
                }
                if (!$request->filled('satim_production_password')) {
                    $prodMissing[] = 'satim_production_password';
                }
                if (!$request->filled('satim_production_terminal')) {
                    $prodMissing[] = 'satim_production_terminal';
                }
                if (count($prodMissing) > 0 && count($prodMissing) < 3) {
                    $validator->errors()->add('satim_production_credentials', 'All production credentials are required together. You are missing ' . implode(', ', $prodMissing) . '.');
                }

                if (!$request->filled('satim_development_username') && !$request->filled('satim_production_username')) {
                    $validator->errors()->add('environment', 'At least one environment (development or production) must be provided.');
                }
            });

            $validator->validate();

            $partnerKey = $request->header('x-partner-key');
            $partnerSecret = $request->header('x-partner-secret');

            $user = User::where('partner_key', $partnerKey)
                ->where('partner_secret', $partnerSecret)
                ->first();

            $license = $user->licenses()->first();

            $license = License::create([
                'name' => $request->name,

                'satim_development_username' => $request->satim_development_username,
                'satim_development_password' => $request->satim_development_password,
                'satim_development_terminal' => $request->satim_development_terminal,

                'satim_production_username' => $request->satim_production_username,
                'satim_production_password' => $request->satim_production_password,
                'satim_production_terminal' => $request->satim_production_terminal,

                'user_id' => $user->id,
                'partner_id' => $user->id,
            ]);

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

            $partnerKey = $request->header('x-partner-key');
            $partnerSecret = $request->header('x-partner-secret');

            $user = User::where('partner_key', $partnerKey)
                ->where('partner_secret', $partnerSecret)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $license = License::where('id', $request->id)
                ->where('user_id', $user->id)
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
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|string',
                'name' => 'nullable|string',
                'satim_development_username' => 'nullable|string',
                'satim_development_password' => 'nullable|string',
                'satim_development_terminal' => 'nullable|string',
                'satim_production_username' => 'nullable|string',
                'satim_production_password' => 'nullable|string',
                'satim_production_terminal' => 'nullable|string',
            ]);

            $validator->after(function ($validator) use ($request) {
                $devMissing = [];
                if ($request->hasAny(['satim_development_username', 'satim_development_password', 'satim_development_terminal'])) {
                    if (!$request->filled('satim_development_username')) {
                        $devMissing[] = 'satim_development_username';
                    }
                    if (!$request->filled('satim_development_password')) {
                        $devMissing[] = 'satim_development_password';
                    }
                    if (!$request->filled('satim_development_terminal')) {
                        $devMissing[] = 'satim_development_terminal';
                    }
                    if (count($devMissing) > 0 && count($devMissing) < 3) {
                        $validator->errors()->add('satim_development_credentials', 'All development credentials are required together. You are missing ' . implode(', ', $devMissing) . '.');
                    }
                }

                $prodMissing = [];
                if ($request->hasAny(['satim_production_username', 'satim_production_password', 'satim_production_terminal'])) {
                    if (!$request->filled('satim_production_username')) {
                        $prodMissing[] = 'satim_production_username';
                    }
                    if (!$request->filled('satim_production_password')) {
                        $prodMissing[] = 'satim_production_password';
                    }
                    if (!$request->filled('satim_production_terminal')) {
                        $prodMissing[] = 'satim_production_terminal';
                    }
                    if (count($prodMissing) > 0 && count($prodMissing) < 3) {
                        $validator->errors()->add('satim_production_credentials', 'All production credentials are required together. You are missing ' . implode(', ', $prodMissing) . '.');
                    }
                }

                if (!$request->filled('satim_development_username') && !$request->filled('satim_production_username')) {
                    $validator->errors()->add('environment', 'At least one environment (development or production) must be provided.');
                }
            });

            $validator->validate();

            $partnerKey = $request->header('x-partner-key');
            $partnerSecret = $request->header('x-partner-secret');

            $user = User::where('partner_key', $partnerKey)
                ->where('partner_secret', $partnerSecret)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $license = License::where('id', $request->id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $license->update([
                'name' => $request->name ?? $license->name,
                'satim_development_username' => $request->satim_development_username,
                'satim_development_password' => $request->satim_development_password,
                'satim_development_terminal' => $request->satim_development_terminal,
                'satim_production_username' => $request->satim_production_username,
                'satim_production_password' => $request->satim_production_password,
                'satim_production_terminal' => $request->satim_production_terminal,
            ]);

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
            $partnerKey = $request->header('x-partner-key');
            $partnerSecret = $request->header('x-partner-secret');

            $user = User::where('partner_key', $partnerKey)
                ->where('partner_secret', $partnerSecret)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $license = License::where('id', $request->id)
                ->where('user_id', $user->id)
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

            $partnerKey = $request->header('x-partner-key');
            $partnerSecret = $request->header('x-partner-secret');

            $partner = User::where('partner-key', $partnerKey)
                ->where('partner_secret', $partnerSecret)
                ->first();

            if (!$partner) {
                throw new \Exception('Unauthorized', 401);
            }

            $license = License::where('id', $request->license_id)
                ->where('user_id', $partner->id)
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
