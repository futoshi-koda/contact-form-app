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

    /** @test */
    public function 正常値_キーワード_性別_カテゴリ_日付_が通過する(): void
    {
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

    /** @test */
    public function 不正値_性別外_存在しないカテゴリ_不正な日付_が拒否される(): void
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

    /** @test */
    public function 新規作成_全必須項目およびタグ入力が通過する(): void
    {
        $category = Category::first();

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

    /** @test */
    public function 新規作成_必須項目の未入力や不正なタグIDが拒否される(): void
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

