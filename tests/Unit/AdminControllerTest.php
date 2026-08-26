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
}