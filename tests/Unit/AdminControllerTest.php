<?php

namespace Tests\Unit;

use App\Http\Requests\AdminRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function お問い合わせ検索でフィルタが有効(): void
    {
        $category = Category::first();

        $data = [
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-08-24',
        ];

        $request = AdminRequest::create('/admin/index', 'GET', $data);
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 不正な性別値の拒否(): void
    {
        $data = [
            'gender' => 99,
        ];

        $request = AdminRequest::create('/admin/index', 'GET', $data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
    }
    /** @test */
    public function 正しいフィルタ条件を受け付けること(): void
    {
        $data = [
            'keyword' => 'テスト',
            'gender' => '1',
            'category_id' => '1',
            'date' => '2026-08-26',
        ];

        // フォームリクエスト（AdminRequestなど）または Validator でルールをチェック
        $rules = [
            'gender' => ['nullable', 'in:0,1,2,3,all'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function 不正な性別や存在しないカテゴリIDを拒否すること(): void
    {
        $data = [
            'gender' => '9',
            'category_id' => '99999',
        ];

        $rules = [
            'gender' => ['nullable', 'in:0,1,2,3,all'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->messages());
        $this->assertArrayHasKey('category_id', $validator->errors()->messages());
    }
}