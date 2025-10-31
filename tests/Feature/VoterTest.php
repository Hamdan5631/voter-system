<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ward;
use App\Models\Voter;
use App\Models\VoterWorkerAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VoterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_superadmin_can_create_voter(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $ward = Ward::factory()->create();
        $token = $superadmin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/voters', [
                'serial_number' => 'VOTER001',
                'ward_id' => $ward->id,
                'panchayat' => 'Panchayat A',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'voter' => ['id', 'serial_number', 'ward_id'],
            ]);

        $this->assertDatabaseHas('voters', [
            'serial_number' => 'VOTER001',
            'ward_id' => $ward->id,
        ]);
    }

    public function test_team_lead_can_view_voters_in_ward(): void
    {
        $ward = Ward::factory()->create();
        $teamLead = User::factory()->create([
            'role' => 'team_lead',
            'ward_id' => $ward->id,
        ]);
        $voter = Voter::factory()->create(['ward_id' => $ward->id]);
        $token = $teamLead->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/voters');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_worker_can_only_view_assigned_voters(): void
    {
        $ward = Ward::factory()->create();
        $worker = User::factory()->create([
            'role' => 'worker',
            'ward_id' => $ward->id,
        ]);
        $voter1 = Voter::factory()->create(['ward_id' => $ward->id]);
        $voter2 = Voter::factory()->create(['ward_id' => $ward->id]);
        
        // Assign only voter1 to worker
        VoterWorkerAssignment::create([
            'voter_id' => $voter1->id,
            'worker_id' => $worker->id,
            'assigned_by' => $worker->id,
        ]);

        $token = $worker->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/voters');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_team_lead_can_update_voter_status(): void
    {
        $ward = Ward::factory()->create();
        $teamLead = User::factory()->create([
            'role' => 'team_lead',
            'ward_id' => $ward->id,
        ]);
        $voter = Voter::factory()->create([
            'ward_id' => $ward->id,
            'status' => false,
        ]);
        $token = $teamLead->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/voters/{$voter->id}/status", [
                'status' => true,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($voter->fresh()->status);
    }

    public function test_worker_can_update_remark_for_assigned_voter(): void
    {
        $ward = Ward::factory()->create();
        $teamLead = User::factory()->create([
            'role' => 'team_lead',
            'ward_id' => $ward->id,
        ]);
        $worker = User::factory()->create([
            'role' => 'worker',
            'ward_id' => $ward->id,
        ]);
        $voter = Voter::factory()->create(['ward_id' => $ward->id]);
        
        VoterWorkerAssignment::create([
            'voter_id' => $voter->id,
            'worker_id' => $worker->id,
            'assigned_by' => $teamLead->id,
        ]);

        $token = $worker->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/voters/{$voter->id}/remark", [
                'remark' => 'Test remark',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('voter_worker_assignments', [
            'voter_id' => $voter->id,
            'worker_id' => $worker->id,
            'remark' => 'Test remark',
        ]);
    }

    public function test_team_lead_can_assign_voter_to_worker(): void
    {
        $ward = Ward::factory()->create();
        $teamLead = User::factory()->create([
            'role' => 'team_lead',
            'ward_id' => $ward->id,
        ]);
        $worker = User::factory()->create([
            'role' => 'worker',
            'ward_id' => $ward->id,
        ]);
        $voter = Voter::factory()->create(['ward_id' => $ward->id]);
        $token = $teamLead->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/voters/{$voter->id}/assign", [
                'worker_id' => $worker->id,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('voter_worker_assignments', [
            'voter_id' => $voter->id,
            'worker_id' => $worker->id,
            'assigned_by' => $teamLead->id,
        ]);
    }

    public function test_voter_can_be_created_with_image(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);
        $ward = Ward::factory()->create();
        $token = $superadmin->createToken('test-token')->plainTextToken;

        $image = UploadedFile::fake()->image('voter.jpg', 1000, 1000);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/voters', [
                'serial_number' => 'VOTER002',
                'ward_id' => $ward->id,
                'panchayat' => 'Panchayat B',
                'image' => $image,
            ]);

        $response->assertStatus(201);
        $voter = Voter::where('serial_number', 'VOTER002')->first();
        $this->assertNotNull($voter->image_path);
        Storage::disk('public')->assertExists($voter->image_path);
    }
}
