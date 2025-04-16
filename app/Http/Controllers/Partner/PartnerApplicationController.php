<?php

namespace App\Http\Controllers\Partner;

use App\Models\User;
use App\Models\License;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesApiExceptions;
use App\Http\Resources\API\ApplicationResource;
use App\Http\Resources\ApplicationResponseResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PartnerApplicationController extends Controller
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

            $partner = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$partner) {
                throw new \Exception('Unauthorized', 401);
            }

            $applications = Application::where('partner_id', $partner->id)->get();

            return response()->json([
                'data' => $applications->isEmpty() ? [] : $applications->map(fn($application) => [
                    'type' => 'application',
                    'id' => $application->id,
                    'attributes' => $application->toArray()
                ]),
                'meta' => [
                    'code' => $applications->isEmpty() ? 'NO_APPLICATIONS_FOUND' : 'APPLICATIONS_FOUND',
                    'message' => $applications->isEmpty() ? 'No applications available' : 'Applications retrieved successfully'
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
                'website_url' => 'required|string',
                'redirect_url' => 'required|string',
            ]);

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $partner = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$partner) {
                throw new \Exception('Unauthorized', 401);
            }

            $license = $partner->licenses()->first();
            $application = Application::create([
                'name' => $request->name,
                'website_url' => $request->website_url,
                'redirect_url' => $request->redirect_url,
                'user_id' => $partner->id,
                'license_id' => $license->id,
                'license_env' => 'development',
                'partner_id' => $partner->id,
            ]);

            return new ApplicationResource([
                'success' => true,
                'code' => 'APPLICATION_CREATED',
                'message' => 'Application savec successfully',
                'data' => $application,
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

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $application = Application::where('id', $request->id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            return new ApplicationResource([
                'success' => true,
                'code' => 'APPLICATION_FOUND',
                'message' => 'Application retrieved successfully',
                'data' => $application,
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
                'website_url' => 'sometimes|required|string',
                'redirect_url' => 'sometimes|required|string',
                'license_id' => 'sometimes|required|string',
                'license_env' => 'sometimes|required|string',
            ]);

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $application = Application::where('id', $request->id)
                ->firstOrFail();

            $application->update($request->only(['name', 'website_url', 'redirect_url', 'license_id', 'license_env']));

            return new ApplicationResource([
                'success' => true,
                'code' => 'APPLICATION_UPDATED',
                'message' => 'Application updated successfully',
                'data' => $application,
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
            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $application = Application::where('id', $request->id)
                ->firstOrFail();

            $application->delete();

            return response()->json([
                'data' => null,
                'meta' => [
                    'code' => 'APPLICATION_DELETED',
                    'message' => 'Application deleted successfully'
                ]
            ], 200);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Assign a license to an application.
     */
    public function assignLicense(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|string',
                'license_id' => 'required|string|exists:licenses,id',
                'license_env' => 'required|string|in:development,production',
            ]);

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $partner = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$partner) {
                throw new \Exception('Unauthorized', 401);
            }

            $application = Application::where('id', $request->id)
                ->where('partner_id', $partner->id)
                ->firstOrFail();

            $license = License::where('id', $request->license_id)
                ->where('user_id', $partner->id)
                ->firstOrFail();

            $application->update([
                'license_id' => $license->id,
                'license_env' => $request->input('license_env', $application->license_env ?? 'development'),
            ]);

            return new ApplicationResource([
                'success' => true,
                'code' => 'LICENSE_ASSIGNED',
                'message' => 'License assigned to application successfully',
                'data' => $application,
                'http' => 200,
            ]);
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
                'application_id' => 'required|string|exists:applications,id',
                'new_user_id' => 'required|string|exists:users,id'
            ]);

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $partner = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$partner) {
                throw new \Exception('Unauthorized', 401);
            }

            $application = Application::where('id', $request->application_id)
                ->where('partner_id', $partner->id)
                ->firstOrFail();

            $newUser = User::findOrFail($request->new_user_id);

            $application->update([
                'user_id' => $newUser->id,
                // 'partner_id' => $newUser->isPartner() ? $newUser->id : null,
            ]);

            return new ApplicationResource([
                'success' => true,
                'code' => 'OWNERSHIP_TRANSFERRED',
                'message' => 'Application ownership transferred successfully',
                'data' => $application,
                'http' => 200,
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }
}
