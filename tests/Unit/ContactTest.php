<?php
namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function １つのお問い合わせが特定のカテゴリに属し複数のタグと同期できる(): void
    {
        // 1. カテゴリとタグを取得
        $category = Category::first();
        $tags = Tag::take(2)->get(); // 同期テスト用にタグを2つ取得

        // 2. テスト用のお問い合わせを作成してカテゴリに紐づける
        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'detail' => 'テスト問い合わせ',
        ]);

        // 【検証1】特定カテゴリに属していること（BelongsTo）
        $this->assertInstanceOf(Category::class, $contact->category);
        $this->assertEquals($category->id, $contact->category->id);

        // 3. タグの同期（sync）を実行
        $contact->tags()->sync($tags->pluck('id'));

        // 【検証2】複数のタグと正しく同期されていること（BelongsToMany）
        $contact->refresh(); // リレーションを最新状態に再読み込み
        $this->assertCount(2, $contact->tags);
        $this->assertTrue($contact->tags->contains($tags->first()));
        $this->assertTrue($contact->tags->contains($tags->last()));
    }
}
