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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
    }
}
