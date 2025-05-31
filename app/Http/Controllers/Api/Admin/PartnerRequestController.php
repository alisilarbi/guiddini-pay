<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesApiExceptions;
use App\Http\Resources\Api\PartnerRequestResource;
use App\Actions\PartnerRequest\CreatePartnerRequest;
use App\Http\Requests\Api\Partner\StorePartnerRequest;

class PartnerRequestController extends Controller
{
    use HandlesApiExceptions;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Logic to list partner requests
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePartnerRequest $request, CreatePartnerRequest $action)
    {
        $partnerRequest = $action->handle(
            data: $request->validated()
        );


        dd($partnerRequest);

        return new PartnerRequestResource($partnerRequest, [
            'meta' => [
                'code' => 'PARTNER_REQUEST_CREATED',
                'message' => 'Partner request created successfully'
            ]
        ]);
        try {
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Logic to show a specific partner request
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Logic to update a partner request
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Logic to delete a partner request
    }
}
