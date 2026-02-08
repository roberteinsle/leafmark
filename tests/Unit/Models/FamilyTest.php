<?php

namespace Tests\Unit\Models;

use App\Models\Family;
use Tests\TestCase;

class FamilyTest extends TestCase
{
    public function test_join_code_auto_generated_on_create(): void
    {
        $owner = $this->createUser();

        $family = Family::create([
            'name' => 'Test Family',
            'owner_id' => $owner->id,
        ]);

        $this->assertNotNull($family->join_code);
    }

    public function test_join_code_is_8_chars_uppercase(): void
    {
        $owner = $this->createUser();

        $family = Family::create([
            'name' => 'Test Family',
            'owner_id' => $owner->id,
        ]);

        $this->assertEquals(8, strlen($family->join_code));
        $this->assertEquals(strtoupper($family->join_code), $family->join_code);
    }

    public function test_is_owner_check(): void
    {
        $owner = $this->createUser();
        $member = $this->createUser();

        $family = Family::create([
            'name' => 'Test Family',
            'owner_id' => $owner->id,
        ]);

        $this->assertTrue($family->isOwner($owner));
        $this->assertFalse($family->isOwner($member));
    }
}
