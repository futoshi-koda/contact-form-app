<?php
namespace Tests\Unit\Models;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function 中間テーブルを介して１つのタグが複数のお問い合わせに紐づいている(): void
    {
        // 1. テスト用のタグを取得
        $tag = Tag::first();

        // 2. お問い合わせを2件作成
        $contacts = Contact::factory()->count(2)->create();

        // 3. 既存の紐付けを保持したまま重複なく紐付ける
        $tag->contacts()->syncWithoutDetaching($contacts->pluck('id'));

        // 4. アサート
        $tag->refresh();

        // 該当のタグにお問い合わせが2件以上紐づいていること
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $tag->contacts);
        $this->assertGreaterThanOrEqual(2, $tag->contacts->count());

        // 指定した2件のお問い合わせが含まれていること
        $this->assertTrue($tag->contacts->contains($contacts->first()));
        $this->assertTrue($tag->contacts->contains($contacts->last()));
    }
}