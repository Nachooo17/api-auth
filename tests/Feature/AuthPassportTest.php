<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_registrar_un_usuario_correctamente()
    {


        $payload = [
            'name' => 'Juan Perez',
            'email' => 'juan@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ];

        $response = $this->postJson('/api/user', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com'
        ]);
    }

    /** @test */
    public function puede_iniciar_sesion_y_recibir_el_token()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('12345678'),
        ]);

        DB::table('oauth_clients')->insert([
            'id' => 2,
            'name' => 'Password Grant Client',
            'secret' => '1KZnvkvo63Ktvs4nE84ZUZjZPx7P9qgPEqSLCNI5',
            'redirect' => 'http://localhost',
            'personal_access_client' => false,
            'password_client' => true,
            'revoked' => false,
        ]);

        $data = [
            'grant_type' => 'password',
            'client_id' => 2,
            'client_secret' => '1KZnvkvo63Ktvs4nE84ZUZjZPx7P9qgPEqSLCNI5',
            'username' => 'login@example.com',
            'password' => '12345678',
            'scope' => '',
        ];

        $response = $this->postJson('/oauth/token', $data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'token_type',
                     'expires_in',
                     'access_token',
                     'refresh_token'
                 ]);
    }

    /** @test */
    public function puede_validar_token_y_acceder_a_ruta_protegida()
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
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/validate');

        $response->assertStatus(200)
                 ->assertJson([
                     'id' => $user->id,
                     'email' => $user->email,
                 ]);
    }
}
