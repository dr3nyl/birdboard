<?php

namespace Tests\Unit;

use App\Models\User;
use Facades\Tests\Setup\ProjectFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{

    //use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @return void
     */
    
    /** @test */
    public function a_user_has_projects()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(Collection::class, $user->projects);
    }

    /** @test */
    public function a_user_has_accessible_projects()
    {
        $john = $this->signIn();
        
        ProjectFactory::ownedBy($john)->create();

        $this->assertCount(1, $john->accessibleProjects());

        $sally = User::factory()->create();
        $nick = User::factory()->create();

        $sallyProject = ProjectFactory::ownedBy($sally)->create();
        $sallyProject->invite($nick);

        $this->assertCount(1, $john->accessibleProjects());

        $sallyProject->invite($john);

        $this->assertCount(2, $john->accessibleProjects());
    }
}
