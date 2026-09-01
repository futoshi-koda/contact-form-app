<?php
namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // 登録されたユーザー（管理者）を用意
        $this->user = User::factory()->create();
    }

    /** @test */
    public function キーワード_性別_カテゴリ_日付フィルタが機能すること(): void
    {
        $category = Category::first();

        // 検索条件に合致する特定の問い合わせを作成
        $targetContact = Contact::create([
            'category_id' => $category->id,
            'first_name' => '検索テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'search_target@example.com',
            'tel' => '09000000000',
            'address' => '東京都',
            'detail' => '詳細テスト',
        ]);

        // 作成したレコードの日付を確実に指定日に更新
        $targetContact->created_at = '2026-08-26 10:00:00';
        $targetContact->save();

        // ログイン状態で GET アクセス
        $response = $this->actingAs($this->user)->get('/admin?' . http_build_query([
            'keyword' => '検索テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-08-26',
        ]));

        $response->assertStatus(200);
        $response->assertSee('検索テスト');
        $response->assertSee('search_target@example.com');
    }

    /** @test */
    public function 検索結果が7件ごとにページネーションされる(): void
    {
        // 10件のダミーデータを作成
        Contact::factory()->count(10)->create();

        $response = $this->actingAs($this->user)->get('/admin');

        $response->assertStatus(200);
        // ビュー変数 contacts の1ページあたりの件数が7件であること
        $response->assertViewHas('contacts', function ($contacts) {
            return $contacts->perPage() === 7;
        });
    }

    /** @test */
    public function 指定したお問い合わせがカテゴリ情報付きで詳細ページに表示される(): void
    {
        $contact = Contact::first();

        // GET /admin/contacts/{contact} へアクセス
        $response = $this->actingAs($this->user)->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertViewIs('admin.show');
        // お問い合わせ情報と紐づくカテゴリコンテンツが表示されていること
        $response->assertSee($contact->first_name);
        $response->assertSee($contact->category->content);
    }

    /** @test */
    public function レコードが正常に削除され_adminにリダイレクトされること(): void
    {
        $contact = Contact::factory()->create();

        // DELETE /admin/contacts/{contact} へリクエスト送信
        $response = $this->actingAs($this->user)->delete("/admin/contacts/{$contact->id}");

        // /admin にリダイレクトされること
        $response->assertRedirect('/admin');

        // 該当レコードが DB から消えていること
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
    /** @test */
    public function ログイン済み管理者がフィルタ条件付きでCSVをダウンロードできること(): void
    {
        // 条件に合うデータと合わないデータを作成
        $matchContact = Contact::factory()->create([
            'first_name' => '該当太郎',
            'gender' => 1,
        ]);
        $unmatchContact = Contact::factory()->create([
            'first_name' => '非該当次郎',
            'gender' => 2,
        ]);

        // フィルタ条件（gender=1）を指定してエクスポートを実行
        $response = $this->actingAs($this->user)->get('/contacts/export?gender=1');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        // 該当するデータのみが含まれ、該当しないデータが含まれていないこと
        $this->assertStringContainsString('該当太郎', $content);
        $this->assertStringNotContainsString('非該当次郎', $content);
    }

    /** @test */
    public function フィルタ無指定時は全件が新着順でCSV出力されること(): void
    {
        // 作成日時に差をつけて2件作成
        $oldContact = Contact::factory()->create([
            'first_name' => '古いデータ',
            'created_at' => now()->subDay(),
        ]);
        $newContact = Contact::factory()->create([
            'first_name' => '新しいデータ',
            'created_at' => now(),
        ]);

        // パラメータなしでエクスポートを実行
        $response = $this->actingAs($this->user)->get('/contacts/export');

        $response->assertStatus(200);

        $content = $response->streamedContent();

        // 両方のデータが出力に含まれていること
        $this->assertStringContainsString('新しいデータ', $content);
        $this->assertStringContainsString('古いデータ', $content);

        // 新しいデータが古いデータよりも前に出力されていること（新着順）
        $this->assertTrue(
            strpos($content, '新しいデータ') < strpos($content, '古いデータ')
        );
    }
}
