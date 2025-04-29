<?php

namespace App\Http\Controllers\Api\Partner;

use App\Models\User;
use App\Models\Prospect;
use App\Models\Application;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesApiExceptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Actions\Prospect\CreateProspect;
use App\Actions\Prospect\DeleteProspect;
use App\Actions\Prospect\UpdateProspect;
use App\Http\Resources\Api\ClientResource;
use App\Http\Resources\ProspectApiResource;
use App\Mail\Partner\NewProspectRegistered;
use App\Http\Resources\Api\ProspectResource;
use App\Http\Requests\Api\Prospect\StoreProspectRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Api\Prospect\UpdateProspectRequest;

class PartnerProspectController extends Controller
{
    use HandlesApiExceptions;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $partner = $request->attributes->get('partner');
            $prospects = Prospect::where('converted', false)
                ->where('partner_id', $partner->id)
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
    public function store(StoreProspectRequest $request, CreateProspect $action)
    {
        try {
            $partner = $request->attributes->get('partner');
            $prospect = $action->handle(
                partner: $partner,
                data: $request->validated()
            );

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

            $partner = $request->attributes->get('partner');

            $prospect = Prospect::where('id', $request->id)
                ->where('converted', false)
                ->where('partner_id', $partner->id)
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
    public function update(UpdateProspectRequest $request, UpdateProspect $action)
    {
        try {
            $partner = $request->attributes->get('partner');

            $prospect = Prospect::where('id', $request->id)
                ->where('partner_id', $partner->id)
                ->where('converted', false)
                ->firstOrFail();

            $updatedProspect = $action->handle(
                prospect: $prospect,
                data: $request->validated()
            );

            return new ProspectResource([
                'success' => true,
                'code' => 'PROSPECT_UPDATED',
                'message' => 'Prospect updated successfully',
                'data' => $updatedProspect,
                'http' => 200,
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, DeleteProspect $action)
    {
        try {
            $request->validate([
                'id' => 'required|string',
            ]);

            $partner = $request->attributes->get('partner');

            $prospect = Prospect::where('id', $request->id)
                ->where('partner_id', $partner->id)
                ->where('converted', false)
                ->firstOrFail();

            if ($prospect->converted) {
                throw new \Exception('PROSPECT_CONVERTED');
            }

            $action->handle(
                prospect: $prospect
            );

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


    /**
     * Convert the prospect to client + app
     *
     */
    public function convert(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required|string',
            ]);

            $partner = $request->attributes->get('partner');

            $prospect = Prospect::where('id', $request->id)
                ->where('partner_id', $partner->id)
                ->firstOrFail();

            if ($prospect->converted) {
                throw new \Exception('PROSPECT_CONVERTED');
            }

            $user = User::where('email', $prospect->email)->first();
            if (!$user) {
                $user = User::create([
                    'name' => $prospect->name,
                    'email' => $prospect->email,
                    'password' => Hash::make(Str::random(12)),
                    'partner_id' => $partner->id,
                    'is_user' => false,
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

            $prospect->update([
                'converted' => true,
                'user_id' => $user->id,
                'application_id' => $application->id,
            ]);

            return new ClientResource([
                'success' => true,
                'code' => 'PROSPECT_CONVERTED',
                'message' => 'Converted to client successfully',
                'data' => $user,
                'http' => 201,
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }
}
