<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function usuario_autenticado_puede_acceder_ruta_protegida()
    {
        DB::table('oauth_clients')->insert([
            'id' => 1,
            'name' => 'Personal Access Client',
            'secret' => 'test-secret',
            'redirect' => 'http://localhost',
            'personal_access_client' => true,
            'password_client' => false,
            'revoked' => false,
        ]);

        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => 1,
        ]);

        $user = User::factory()->create();
        $token = $user->createToken('TestToken')->accessToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token
        ])->getJson('/api/validate');

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'email' => $user->email,
                     'name' => $user->name,
                 ]);
    }
}
