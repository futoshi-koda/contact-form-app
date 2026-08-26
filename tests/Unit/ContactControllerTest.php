<?php

namespace Tests\Unit;

use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // シーダーを実行して Category や Tag などの初期データを準備
        $this->seed();
    }

    /** @test */
    public function 必須項目とタグは受け入れる(): void
    {
        // 存在するカテゴリとタグのIDを取得
        $category = Category::first();
        $tag = Tag::first();

        // テスト用データ（必須項目 ＋ タグ）
        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678', // 正常な電話番号
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'テスト',
            'tag_ids' => [$tag->id],
        ];

        $request = ContactRequest::create('/contacts', 'POST', $data);
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 不正な電話番号は拒否する(): void
    {
        $data = [
            'tel' => 'abc',
        ];

        $request = ContactRequest::create('/contacts', 'POST', $data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
    }
}
