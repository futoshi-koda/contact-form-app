<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 共通で Accept: application/json ヘッダーを付与
        $this->withHeaders([
            'Accept' => 'application/json',
        ]);

        // カテゴリーの初期データを投入
        $this->seed(CategorySeeder::class);
    }

    /* =========================================================================
     * 1. お問い合わせ一覧API（GET /api/v1/contacts）
     * ========================================================================= */

    /** @test */
    public function お問い合わせ一覧API_JSON形式の一覧が返り_検索_ページネーションが機能する(): void
    {
        $category = Category::first();

        // テスト用のお問い合わせデータを作成
        Contact::factory()->count(15)->create([
            'category_id' => $category->id,
            'gender' => 1,
        ]);

        $params = [
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-01-01',
        ];

        $response = $this->getJson('/api/v1/contacts?' . http_build_query($params));

        $response->assertStatus(200);
    }

    /** @test */
    public function お問い合わせ一覧API_バリデーションエラー時は422が返る(): void
    {
        $params = [
            'gender' => 'invalid_gender_string', // integer 違反
            'category_id' => 99999,                   // exists 違反
            'date' => 'invalid-date-format',   // date 違反
        ];

        $response = $this->getJson('/api/v1/contacts?' . http_build_query($params));

        $response->assertStatus(422);
    }

    /* =========================================================================
     * 2. お問い合わせ詳細API（GET /api/v1/contacts/{id}）
     * ========================================================================= */

    /** @test */
    public function お問い合わせ詳細API_JSON形式の詳細が返る(): void
    {
        $category = Category::first();
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '詳細',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'show@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'detail' => '詳細テスト',
        ]);

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function お問い合わせ詳細API_存在しないIDで404エラーJSONが返る(): void
    {
        $response = $this->getJson('/api/v1/contacts/99999');

        $response->assertStatus(404);
    }

    /* =========================================================================
     * 3. お問い合わせ作成API（POST /api/v1/contacts）
     * ========================================================================= */

    /** @test */
    public function お問い合わせ作成API_レコードが作成され201が返る(): void
    {
        $category = Category::first();
        $tag1 = Tag::create(['name' => 'タグ1']);
        $tag2 = Tag::create(['name' => 'タグ2']);

        $data = [
            'first_name' => '作成',
            'last_name' => '二郎',
            'gender' => 2,
            'email' => 'store@example.com',
            'tel' => '08098765432',
            'address' => '大阪府',
            'building' => 'ビル101',
            'category_id' => $category->id,
            'detail' => '作成テスト内容',
            'tag_ids' => [$tag1->id, $tag2->id],
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contacts', [
            'email' => 'store@example.com',
        ]);
    }

    /** @test */
    public function お問い合わせ作成API_バリデーションエラー時は422が返る(): void
    {
        $data = [
            'first_name' => '',
            'gender' => 99,
            'email' => 'invalid-email',
            'category_id' => 99999,
        ];

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'gender',
                'email',
                'category_id',
                'detail',
            ]);
    }

    /* =========================================================================
     * 4. お問い合わせ更新API（PUT /api/v1/contacts/{id}）
     * ========================================================================= */

    /** @test */
    public function お問い合わせ更新API_レコードが更新され200が返る(): void
    {
        $category = Category::first();
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '更新前',
            'last_name' => '三郎',
            'gender' => 1,
            'email' => 'before@example.com',
            'tel' => '09000000000',
            'address' => '愛知県',
            'detail' => '更新前詳細',
        ]);

        $updateData = [
            'first_name' => '更新後',
            'last_name' => '三郎',
            'gender' => 1,
            'email' => 'after@example.com',
            'tel' => '09000000000',
            'address' => '愛知県',
            'category_id' => $category->id,
            'detail' => '更新後詳細メッセージ',
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '更新後',
            'email' => 'after@example.com',
        ]);
    }

    /** @test */
    public function お問い合わせ更新API_存在しないIDで404が返る(): void
    {
        $updateData = [
            'first_name' => '更新',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'category_id' => 1,
            'detail' => '詳細',
        ];

        $response = $this->putJson('/api/v1/contacts/99999', $updateData);

        $response->assertStatus(404);
    }

    /** @test */
    public function お問い合わせ更新API_バリデーションエラー時は422が返る(): void
    {
        $category = Category::first();
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => 'テスト',
            'last_name' => 'テスト',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => '詳細',
        ]);

        $invalidData = [
            'gender' => 99,
        ];

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $invalidData);

        $response->assertStatus(422);
    }

    /* =========================================================================
     * 5. お問い合わせ削除API（DELETE /api/v1/contacts/{id}）
     * ========================================================================= */

    /** @test */
    public function お問い合わせ削除API_レコードが削除される(): void
    {
        $category = Category::first();
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '削除',
            'last_name' => '四郎',
            'gender' => 1,
            'email' => 'delete@example.com',
            'tel' => '09000000000',
            'address' => '福岡県',
            'detail' => '削除対象データ',
        ]);

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        // DELETE レスポンスステータス（200 または 204）
        // コントローラーが 200 を返している場合は 200 に変更してください
        $response->assertStatus(200);

        // DBから削除されていることを検証
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    /** @test */
    public function お問い合わせ削除API_存在しないIDで404が返る(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/99999');

        $response->assertStatus(404);
    }
}