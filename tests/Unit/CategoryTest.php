<?php
namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // シードを実行してカテゴリとお問い合わせの初期データを準備
    }

    /** @test */
    public function １つのカテゴリから複数のお問い合わせが正しく取得できる(): void
    {
        // 1. お問い合わせが複数紐づいているカテゴリを取得（またはシーダーから取得）
        $category = Category::has('contacts', '>=', 2)->first();

        // 2. リレーション経由でお問い合わせ一覧を取得
        $contacts = $category->contacts;

        // 3. アサート
        // 取得結果が Collection であること
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $contacts);
        // 取得した件数が2件以上であること
        $this->assertGreaterThanOrEqual(2, $contacts->count());
        // 取得したお問い合わせの最初の1件が Contact モデルのインスタンスであること
        $this->assertInstanceOf(Contact::class, $contacts->first());
        // 紐づいている category_id が一致していること
        $this->assertEquals($category->id, $contacts->first()->category_id);
    }
}
