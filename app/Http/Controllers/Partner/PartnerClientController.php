<?php

namespace App\Http\Controllers\Partner;

use App\Models\User;
use App\Models\Prospect;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesApiExceptions;
use App\Http\Resources\API\ClientResource;

class PartnerClientController extends Controller
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

            $clients = User::where('partner_id', $user->id)->get();

            return response()->json([
                'data' => $clients->isEmpty() ? [] : $clients->map(fn($client) => [
                    'type' => 'client',
                    'id' => $client->id,
                    'attributes' => $client->toArray()
                ]),
                'meta' => [
                    'code' => $clients->isEmpty() ? 'NO_CLIENTS_FOUND' : 'CLIENTS_FOUND',
                    'message' => $clients->isEmpty() ? 'No clients found' : 'Clients retrieved successfully'
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
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string',
            ]);

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $client = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'partner_id' => $user->id,
                'is_admin' => false,
                'is_partner' => false,
                'partner_id' => $user->id,
                'password' => $request->password,
                'reset_password_flag' => true,
            ]);

            return new ClientResource([
                'success' => true,
                'code' => 'CLIENT_CREATED',
                'message' => 'Client created successfully',
                'data' => $client,
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

            $client = User::where('id', $request->id)
                // ->where('partner_id', $user->id)
                ->firstOrFail();

            return new ClientResource([
                'success' => true,
                'code' => 'CLIENT_FOUND',
                'message' => 'Client retrieved successfully',
                'data' => $client,
                'http_code' => 200,
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
                'email' => 'sometimes|required|email'
            ]);

            $appKey = $request->header('x-app-key');
            $secretKey = $request->header('x-secret-key');

            $user = User::where('app_key', $appKey)
                ->where('app_secret', $secretKey)
                ->first();

            if (!$user) {
                throw new \Exception('Unauthorized', 401);
            }

            $client = User::where('id', $request->id)
                ->where('partner_id', $user->id)
                ->firstOrFail();

            $client->update($request->only(['name', 'email',]));

            return new ClientResource([
                'success' => true,
                'code' => 'CLIENT_UPDATED',
                'message' => 'Client updated successfully',
                'data' => $client,
                'http_code' => 200,
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

            $client = User::where('id', $request->id)
                ->where('partner_id', $user->id)
                ->firstOrFail();

            $apps = [];
            if ($client->applications->isNotEmpty()) {
                foreach ($client->applications as $app) {
                    $app->update([
                        'user_id' =>  $user->id
                    ]);

                    $apps[] = [
                        'type' => 'application',
                        'id' => $app->id,
                    'attributes' => [
                            'name' => $app->name,
                            'license_id' => $app->license_id,
                            'license_env' => $app->license_env
                        ]
                    ];
                }
            }

            $client->delete();

            return response()->json([
                'data' => $apps ? [
                    'message' => 'Some applications were attached to this client and have been detached.',
                    'applications' => $apps
                ] : null,
                'meta' => [
                    'code' => 'CLIENT_DELETED',
                    'message' => $apps ? 'Client deleted successfully, and applications were transfered to you.' : 'Client deleted successfully.'
                ]
            ], 200);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }
}
