<?php

namespace Tests\Feature;

use App\Models\Family;
use Tests\TestCase;

class FamilyTest extends TestCase
{
    public function test_user_can_view_family_page(): void
    {
        $this->actingAsUser();

        $this->get('/family')->assertStatus(200);
    }

    public function test_user_can_create_family(): void
    {
        $user = $this->actingAsUser();

        $response = $this->post('/family', [
            'name' => 'Test Family',
        ]);

        $response->assertRedirect();
        $family = Family::first();
        $this->assertNotNull($family);
        $this->assertEquals('Test Family', $family->name);
        $this->assertEquals($user->id, $family->owner_id);
        $this->assertNotNull($family->join_code);
    }

    public function test_user_in_family_cannot_create_another(): void
    {
        $user = $this->actingAsUser();
        $family = Family::create(['name' => 'Existing', 'owner_id' => $user->id]);
        $user->update(['family_id' => $family->id]);

        $response = $this->post('/family', [
            'name' => 'New Family',
        ]);

        $response->assertRedirect();
        $this->assertEquals(1, Family::count());
    }

    public function test_user_can_join_family_with_code(): void
    {
        $owner = $this->createUser();
        $family = Family::create(['name' => 'Test Family', 'owner_id' => $owner->id]);
        $owner->update(['family_id' => $family->id]);

        $user = $this->actingAsUser();

        $response = $this->post('/family/join', [
            'join_code' => $family->join_code,
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals($family->id, $user->family_id);
    }

    public function test_join_fails_with_invalid_code(): void
    {
        $this->actingAsUser();

        $response = $this->post('/family/join', [
            'join_code' => 'INVALID1',
        ]);

        $response->assertRedirect();
    }

    public function test_member_can_leave_family(): void
    {
        $owner = $this->createUser();
        $family = Family::create(['name' => 'Test Family', 'owner_id' => $owner->id]);
        $owner->update(['family_id' => $family->id]);

        $user = $this->actingAsUser();
        $user->update(['family_id' => $family->id]);

        $response = $this->post('/family/leave');

        $response->assertRedirect();
        $user->refresh();
        $this->assertNull($user->family_id);
    }

    public function test_owner_cannot_leave_with_other_members(): void
    {
        $owner = $this->actingAsUser();
        $family = Family::create(['name' => 'Test Family', 'owner_id' => $owner->id]);
        $owner->update(['family_id' => $family->id]);

        $member = $this->createUser();
        $member->update(['family_id' => $family->id]);

        $response = $this->post('/family/leave');

        $response->assertRedirect();
        $owner->refresh();
        $this->assertEquals($family->id, $owner->family_id);
    }

    public function test_owner_can_regenerate_code(): void
    {
        $owner = $this->actingAsUser();
        $family = Family::create(['name' => 'Test Family', 'owner_id' => $owner->id]);
        $owner->update(['family_id' => $family->id]);

        $oldCode = $family->join_code;

        $this->post('/family/regenerate-code');

        $family->refresh();
        $this->assertNotEquals($oldCode, $family->join_code);
    }

    public function test_non_owner_cannot_regenerate_code(): void
    {
        $owner = $this->createUser();
        $family = Family::create(['name' => 'Test Family', 'owner_id' => $owner->id]);
        $owner->update(['family_id' => $family->id]);

        $member = $this->actingAsUser();
        $member->update(['family_id' => $family->id]);

        $oldCode = $family->join_code;

        $this->post('/family/regenerate-code');

        $family->refresh();
        $this->assertEquals($oldCode, $family->join_code);
    }

    public function test_owner_can_destroy_family(): void
    {
        $owner = $this->actingAsUser();
        $family = Family::create(['name' => 'Test Family', 'owner_id' => $owner->id]);
        $owner->update(['family_id' => $family->id]);

        $this->delete('/family');

        $this->assertDatabaseMissing('families', ['id' => $family->id]);
    }
}
