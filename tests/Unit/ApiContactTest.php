<?php

namespace Tests\Unit;

use App\Http\Requests\AdminRequest;
use App\Http\Requests\Api\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ApiContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用データベースにカテゴリーの初期データを投入
        $this->seed(CategorySeeder::class);
    }

    /* =========================================================================
     * 1. AdminRequest（検索・フィルタのバリデーション単体テスト）
     * ========================================================================= */

    /**
     * AdminRequest: 正常値（キーワード、性別、カテゴリ、日付）が通過すること
     */
    public function test_admin_request_passes_with_valid_data(): void
    {
        // Seederで投入された最初のカテゴリーを取得 (ID: 1)
        $category = Category::first();

        $data = [
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-01-01',
        ];

        $request = new AdminRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * AdminRequest: 不正な値（性別外、存在しないカテゴリ、不正な日付）が拒否されること
     */
    public function test_admin_request_fails_with_invalid_data(): void
    {
        $data = [
            'gender' => 99,             // in 違反
            'category_id' => 99999,          // exists 違反
            'date' => 'invalid-date', // date 違反
        ];

        $request = new AdminRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->messages();

        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('category_id', $errors);
        $this->assertArrayHasKey('date', $errors);
    }

    /* =========================================================================
     * 2. StoreContactRequest（新規作成のバリデーション単体テスト）
     * ========================================================================= */

    /**
     * StoreContactRequest: 全必須項目およびタグID入力が通過すること
     */
    public function test_store_contact_request_passes_with_valid_data(): void
    {
        $category = Category::first();

        // テスト用のタグをDBに作成（TagFactoryを使わず直接作成）
        $tag1 = Tag::create(['name' => 'テストタグ1']);
        $tag2 = Tag::create(['name' => 'テストタグ2']);

        $data = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区1-1-1',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => 'これはお問い合わせのテスト詳細メッセージです。',
            'tag_ids' => [$tag1->id, $tag2->id],
        ];

        $request = new StoreContactRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    /**
     * StoreContactRequest: 必須項目の未入力や不正なタグIDが拒否されること
     */
    public function test_store_contact_request_fails_with_invalid_data(): void
    {
        $data = [
            'first_name' => '',              // required 違反
            'last_name' => '',              // required 違反
            'gender' => 99,              // in 違反
            'email' => 'invalid-email', // email 違反
            'category_id' => 99999,           // exists 違反
            'detail' => '',              // required 違反
            'tag_ids' => [99999],         // exists 違反
        ];

        $request = new StoreContactRequest();
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->messages();

        $this->assertArrayHasKey('first_name', $errors);
        $this->assertArrayHasKey('last_name', $errors);
        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('category_id', $errors);
        $this->assertArrayHasKey('detail', $errors);
        $this->assertArrayHasKey('tag_ids.0', $errors);
    }
}

