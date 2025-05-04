<?php

use App\Models\Application;
use App\Models\EventHistory;
use App\Models\QuotaTransaction;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('event_type');
            $table->string('event_code');
            $table->string('event_summary');

            $table->uuidMorphs('eventable'); // Polymorphic relationship (eventable_id, eventable_type)

            $table->string('action'); // e.g., Created, Updated, Paid
            $table->string('payment_status')->nullable(); // e.g., Paid, Unpaid, Production
            $table->decimal('price', 10, 2)->default(0.00); // Cost in DZD
            $table->decimal('quantity', 10, 2)->default(0.00); // Previous cost in DZD
            $table->integer('total')->default(0); // e.g., number of apps or transaction total
            $table->json('details')->nullable();

            $table->uuid('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

            $table->uuid('partner_id')->nullable();
            $table->foreign('partner_id')->references('id')->on('users');

            $table->timestamps();
        });

        // $this->seed();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_histories');
    }


    public function seed()
    {
        $applications = Application::with('quotaTransaction')->get()->map(function ($app) {
            return [
                'type' => 'Application',
                'model' => $app,
                'created_at' => $app->created_at,
            ];
        });

        $quotaTransactions = QuotaTransaction::all()->map(function ($transaction) {
            return [
                'type' => 'QuotaTransaction',
                'model' => $transaction,
                'created_at' => $transaction->created_at,
            ];
        });

        $events = $applications->merge($quotaTransactions)
            ->sortBy('created_at')
            ->values();

        foreach ($events as $event) {
            $model = $event['model'];
            if ($event['type'] === 'Application') {
                EventHistory::create([
                    'event_type' => 'Application',
                    'eventable_id' => $model->id,
                    'eventable_type' => Application::class,
                    'action' => 'Created',
                    'status' => $model->is_paid ? 'Paid' : 'Unpaid',
                    'price' => $model->quotaTransaction ? $model->quotaTransaction->application_price : 0.00,
                    'quantity' => 1.00,
                    'total' => $model->quotaTransaction ? $model->quotaTransaction->application_price : 0.00,
                    'details' => [
                        'application_name' => $model->name,
                        'license_env' => $model->license_env,
                    ],
                    'user_id' => $model->user_id,
                    'partner_id' => $model->partner_id,
                    'created_at' => $model->created_at,
                    'updated_at' => $model->updated_at,
                ]);
            } else {
                EventHistory::create([
                    'event_type' => 'QuotaTransaction',
                    'eventable_id' => $model->id,
                    'eventable_type' => QuotaTransaction::class,
                    'action' => $model->type === 'admin_grant' ? 'Admin Grant' : 'Purchase',
                    'status' => $model->is_paid ? 'Paid' : 'Unpaid',
                    'price' => $model->application_price,
                    'quantity' => $model->quantity,
                    'total' => $model->total,
                    'details' => [
                        'quantity' => $model->quantity,
                        'application_price' => $model->application_price,
                    ],
                    'user_id' => null, // Consistent with QuotaTransactionObserver
                    'partner_id' => $model->partner_id,
                    'created_at' => $model->created_at,
                    'updated_at' => $model->updated_at,
                ]);
            }
        }
    }
};
