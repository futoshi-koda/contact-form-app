<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function お問い合わせ入力フォームが正常に表示されカテゴリやタグが渡されていること(): void
    {
        $category = Category::first();
        $tag = Tag::first();

        // フォーム画面にアクセス
        $response = $this->get('/');

        // 1. 正常表示（200 OK）
        $response->assertStatus(200);

        // 2. categories・tags がビュー変数として渡されていること
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');

        // 3 & 4. カテゴリ名・タグ名がページに表示されていること
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    /** @test */
    public function POST_contacts_confirmでバリデーション通過時にお問い合わせフォーム確認ページが表示され入力内容が表示されること(): void
    {
        $category = Category::first();
        $tag = Tag::first();

        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'category_id' => $category->id,
            'detail' => 'テスト',
            'tag_ids' => [$tag->id],
        ];

        // 6. POST /contacts/confirm へ送信
        $response = $this->post('/contacts/confirm', $data);

        // 6. 確認ページ（contact.confirm）が表示されること
        $response->assertStatus(200);
        $response->assertViewIs('contact.confirm');

        // 7. 入力内容（氏名・メール・カテゴリ名等）が表示されていること
        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('test@example.com');
        $response->assertSee($category->content);
    }
    /**
     * @test
     * @dataProvider invalidContactDataProvider
     */
    public function 確認画面遷移時に各種バリデーションエラーが返ること(array $invalidData, array $expectedErrors): void
    {
        $response = $this->post('/contacts/confirm', $invalidData);

        // 指定したフィールドにエラーが発生していることを確認
        $response->assertSessionHasErrors($expectedErrors);
    }

    /**
     * ContactRequest のルール・メッセージを網羅するテストデータセット
     */
    public static function invalidContactDataProvider(): array
    {
        return [
            // --------------------------------------------------
            // 必須チェック（required）
            // --------------------------------------------------
            'first_name未入力' => [['first_name' => ''], ['first_name']],
            'last_name未入力' => [['last_name' => ''], ['last_name']],
            'gender未選択' => [['gender' => ''], ['gender']],
            'email未入力' => [['email' => ''], ['email']],
            'tel未入力' => [['tel' => ''], ['tel']],
            'address未入力' => [['address' => ''], ['address']],
            'category_id未選択' => [['category_id' => ''], ['category_id']],
            'detail未入力' => [['detail' => ''], ['detail']],

            // --------------------------------------------------
            // 形式・型・存在チェック（email, integer, in, regex, exists）
            // --------------------------------------------------
            'gender形式不適切(文字列)' => [['gender' => 'abc'], ['gender']],
            'gender範囲外(1~3以外)' => [['gender' => 4], ['gender']],
            'email形式不適切' => [['email' => 'invalid-email-format'], ['email']],
            'tel桁数不足(9桁)' => [['tel' => '090123456'], ['tel']],
            'tel桁数超え(12桁)' => [['tel' => '090123456789'], ['tel']],
            'telハイフン含む/文字混入' => [['tel' => '090-1234-5678'], ['tel']],
            'category_id存在しないID' => [['category_id' => 99999], ['category_id']],

            // --------------------------------------------------
            // 文字数制限チェック（max）
            // --------------------------------------------------
            'first_name文字数超過(256文字)' => [['first_name' => str_repeat('あ', 256)], ['first_name']],
            'last_name文字数超過(256文字)' => [['last_name' => str_repeat('あ', 256)], ['last_name']],
            'email文字数超過(256文字)' => [['email' => str_repeat('a', 246) . '@example.com'], ['email']],
            'address文字数超過(256文字)' => [['address' => str_repeat('あ', 256)], ['address']],
            'building文字数超過(256文字)' => [['building' => str_repeat('あ', 256)], ['building']],
            'detail文字数超過(121文字)' => [['detail' => str_repeat('あ', 121)], ['detail']],
        ];
    }

    /** @test */
    public function Thanksページが表示されること(): void
    {
        $category = Category::first();
        $tag = Tag::first();

        // 必須項目データを準備
        $data = [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'detail' => 'テスト問い合わせ本文',
            'tag_ids' => [$tag->id],
        ];

        // POST /contacts にデータを送信して Thanks ページを表示させる
        $response = $this->post('/contacts', $data);

        // 200 OK で正常表示されること
        $response->assertStatus(200);
    }
}