<?php

namespace Tests\Feature\Api;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\VirusScanStatus;
use App\Models\DataFile;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('doctorstats.payment_stub_enabled', true);
        Storage::fake('local');
    }

    public function test_user_can_get_payment_quote_and_create_stub_checkout(): void
    {
        $user = User::factory()->create();
        $dataFile = DataFile::factory()->create([
            'user_id' => $user->id,
            'virus_scan_status' => VirusScanStatus::Clean,
            'file_size_bytes' => 15 * 1_048_576,
        ]);

        $this->actingAs($user)
            ->getJson("/api/v1/payments/quote?data_file_id={$dataFile->id}")
            ->assertOk()
            ->assertJsonPath('data.currency', 'INR')
            ->assertJsonPath('data.breakdown.base_amount', 999);

        $response = $this->actingAs($user)->postJson('/api/v1/payments/checkout', [
            'data_file_id' => $dataFile->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', PaymentStatus::Pending->value)
            ->assertJsonStructure(['data' => ['payment_id', 'checkout_url']]);

        $paymentId = $response->json('data.payment_id');

        $this->actingAs($user)
            ->postJson("/api/v1/payments/{$paymentId}/stub-confirm")
            ->assertOk()
            ->assertJsonPath('data.status', PaymentStatus::Succeeded->value);
    }

    public function test_analysis_job_accepts_confirmed_payment_id(): void
    {
        Http::fake([
            '*/api/v1/analyze' => Http::response(['analysis_id' => 'DS-TEST'], 202),
            '*' => Http::response(['message' => 'not implemented'], 404),
        ]);

        $user = User::factory()->create();
        $dataFile = DataFile::factory()->create([
            'user_id' => $user->id,
            'virus_scan_status' => VirusScanStatus::Clean,
        ]);

        Storage::disk('local')->put($dataFile->s3_path, "patient_id,age,group\n1,45,A\n2,52,B\n");

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'zoho_payment_id' => 'stub-test-payment',
            'amount' => 999,
            'currency' => 'INR',
            'status' => PaymentStatus::Succeeded,
            'payment_type' => 'pay_per_job',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/analysis-jobs', [
            'data_file_id' => $dataFile->id,
            'objectives' => str_repeat('Analyze treatment outcomes across patient groups. ', 2),
            'access_scope' => 'private',
            'payment_method' => PaymentMethod::PayPerJob->value,
            'payment_id' => $payment->id,
            'columns' => [
                ['column_name' => 'patient_id', 'column_index' => 0, 'data_type' => 'text', 'variable_type' => 'identifier'],
                ['column_name' => 'age', 'column_index' => 1, 'data_type' => 'numerical', 'variable_type' => 'independent'],
                ['column_name' => 'group', 'column_index' => 2, 'data_type' => 'categorical', 'variable_type' => 'dependent'],
            ],
        ]);

        $response->assertCreated();
    }

    public function test_zoho_webhook_marks_payment_succeeded(): void
    {
        Config::set('services.zoho_pay.webhook_secret', '');

        $user = User::factory()->create();

        $payment = Payment::query()->create([
            'user_id' => $user->id,
            'zoho_payment_id' => 'zoho-pay-123',
            'amount' => 999,
            'currency' => 'INR',
            'status' => PaymentStatus::Pending,
            'payment_type' => 'pay_per_job',
        ]);

        $this->postJson('/internal/v1/webhooks/zoho', [
            'payment_id' => 'zoho-pay-123',
            'status' => 'succeeded',
            'reference_id' => $payment->id,
        ])->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Succeeded->value,
        ]);
    }
}
