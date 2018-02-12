<?php

namespace Tests\Feature;

use \App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTest extends TestCase
{
	use DatabaseTransactions;

    public function testRequiresEmail()
    {
    	$this->json('POST', '/login')
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                	'email' => 'Bad email. No user with this e-mail'
                ],
            ]);
    }

    public function testRequiresPassword()
    {
    	$user = factory(User::class)->create([
	        'email' => 'testlogin@user.com',
	        'password' => bcrypt('toptal123')
	    ]);

    	$payload = ['email' => 'testlogin@user.com'];

        $this->json('POST', '/login', $payload)
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                	'email' => 'Bad email. No user with this e-mail'
                ],
            ]);
    }

//    public function testUserLoginsSuccessfully()
//    {
//        $user = factory(User::class)->create([
//            'email' => 'testlogin@user.com',
//            'password' => bcrypt('toptal123'),
//        ]);
//
//        $payload = ['email' => 'testlogin@user.com', 'password' => 'toptal123'];
//
//        $this->json('POST', '/login', $payload)
//            ->assertStatus(200);
////            ->assertJsonStructure([
////                'data' => [
////                    'id',
////                    'name',
////                    'email',
////                    'created_at',
////                    'updated_at',
////                    'api_token',
////                ],
////            ]);
//
//    }
}
