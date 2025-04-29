<?php

namespace App\Http\Controllers\Api\Partner;

use App\Models\User;
use App\Models\Prospect;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Actions\Client\CreateClient;
use App\Actions\Client\DeleteClient;
use App\Actions\Client\UpdateClient;
use App\Http\Controllers\Controller;
use App\Traits\HandlesApiExceptions;
use App\Http\Resources\Api\ClientResource;
use App\Http\Requests\Api\Client\UpdateClientRequest;

class PartnerClientController extends Controller
{
    use HandlesApiExceptions;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $partner = $request->attributes->get('partner');
            $clients = User::where('partner_id', $partner->id)->get();

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
    public function store(Request $request, CreateClient $action)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string',
            ]);

            $partner = $request->attributes->get('partner');
            $client = $action->handle(
                partner: $partner,
                data: [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                ]
            );

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
     * Display the specified resource
     */
    public function show(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|string',
            ]);

            $partner = $request->attributes->get('partner');
            $client = User::where('id', $request->id)
                ->where('partner_id', $partner->id)
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
    public function update(UpdateClientRequest $request, UpdateClient $action)
    {

        try {
            $partner = $request->attributes->get('partner');
            $client = User::where('id', $request->id)
                ->where('partner_id', $partner->id)
                ->firstOrFail();

            $action->handle(
                client: $client,
                data: $request->validated(),
            );

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
    public function destroy(Request $request, DeleteClient $action)
    {
        try {
            $request->validate([
                'id' => 'required|string',
            ]);

            $partner = $request->attributes->get('partner');
            $client = User::where('id', $request->id)
                ->where('partner_id', $partner->id)
                ->firstOrFail();

            $apps = $action->handle(
                client: $client,
                partner: $partner,
            );

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
