<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Application;
use Illuminate\Http\Request;
use App\Traits\HandlesApiExceptions;
use App\Http\Resources\API\ApplicationResource;
use App\Http\Resources\ApplicationResponseResource;

class ApplicationController extends Controller
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

            $applications = Application::where('user_id', $user->id)->get();

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

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            $license = $user->licenses()->first();

            $application = Application::create([
                'name' => $request->name,
                'website_url' => $request->website_url,
                'redirect_url' => $request->redirect_url,
                'user_id' => $user->id,
                'license_id' => $license->id,
                'license_env' => 'development',
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

            $application->update($request->only(['name', 'website_url', 'redirect_url']));

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
                ->where('user_id', $user->id)
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
}
