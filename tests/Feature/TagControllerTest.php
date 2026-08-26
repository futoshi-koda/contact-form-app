<?php
namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // ログイン用のユーザーを作成
        $this->user = User::factory()->create();
    }

    /** @test */
    public function 認証済みユーザーがタグ編集画面を表示できること(): void
    {
        $tag = Tag::first();

        $response = $this->actingAs($this->user)->get("/admin/tags/{$tag->id}/edit");

        $response->assertStatus(200);
        $response->assertSee($tag->name);
    }

    /** @test */
    public function 認証済みユーザーがタグを作成できadminにリダイレクトされること(): void
    {
        $tagData = ['name' => '新規テストタグ'];

        $response = $this->actingAs($this->user)->post('/admin/tags', $tagData);

        // /admin にリダイレクトされること
        $response->assertRedirect('/admin');

        // データベースに保存されていること
        $this->assertDatabaseHas('tags', [
            'name' => '新規テストタグ',
        ]);
    }

    /** @test */
    public function 認証済みユーザーがタグを更新できadminにリダイレクトされること(): void
    {
        $tag = Tag::first();
        $updateData = ['name' => '更新後のタグ名'];

        $response = $this->actingAs($this->user)->put("/admin/tags/{$tag->id}", $updateData);

        // /admin にリダイレクトされること
        $response->assertRedirect('/admin');

        // データベースの値が更新されていること
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新後のタグ名',
        ]);
    }

    /** @test */
    public function 認証済みユーザーがタグを削除できadminにリダイレクトされること(): void
    {
        // TagFactory の代わりに Tag::create でテスト用タグを作成
        $tag = Tag::create(['name' => '削除テスト用タグ']);

        $response = $this->actingAs($this->user)->delete("/admin/tags/{$tag->id}");

        // /admin にリダイレクトされること
        $response->assertRedirect('/admin');

        // データベースから削除されていること
        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    /** @test */
    public function 未認証ユーザーのタグ操作が拒否されloginにリダイレクトされること(): void
    {
        $tag = Tag::first();

        // タグ作成
        $this->post('/admin/tags', ['name' => 'テスト'])->assertRedirect('/login');

        // タグ更新
        $this->put("/admin/tags/{$tag->id}", ['name' => 'テスト'])->assertRedirect('/login');

        // タグ削除
        $this->delete("/admin/tags/{$tag->id}")->assertRedirect('/login');
    }
}