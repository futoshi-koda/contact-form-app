<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seederを実行して初期データを準備
        $this->seed();
    }

    /** @test */
    public function ログイン画面が表示されること(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function ユーザー登録処理が正常に実行されること(): void
    {
        $response = $this->post('/register', [
            'name' => '新規登録ユーザー',
            'email' => 'new_user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録後に管理画面へリダイレクトされること
        $response->assertRedirect('/admin/index');

        // DBにユーザーが保存されていること
        $this->assertDatabaseHas('users', [
            'email' => 'new_user@example.com',
        ]);
    }

    /** @test */
    public function UserSeederの管理者情報で正常にログインできること(): void
    {
        // UserSeeder で作成された 'test@example.com' / 'password' でログイン
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // /admin にリダイレクトされること
        $response->assertRedirect('/admin/index');

        // ログイン状態になっていること
        $this->assertAuthenticated();
    }

    /** @test */
    public function ログイン済みユーザーがログイン画面にアクセスすると管理画面にリダイレクトされること(): void
    {
        $user = User::where('email', 'test@example.com')->first();

        // ログイン状態で /login にアクセス
        $response = $this->actingAs($user)->get('/login');

        // /admin にリダイレクトされること
        $response->assertRedirect('/admin/index');
    }
}