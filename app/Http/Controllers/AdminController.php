<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Models\Tag;
use App\Models\Category;
use App\Models\Contact;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(AdminRequest $request)
    {
        $categories = Category::all();
        $tags = Tag::all();

        // 検索パラメータの取得
        $keyword = $request->input('keyword');
        $gender = $request->input('gender');
        $categoryId = $request->input('category_id');
        $date = $request->input('date');

        // クエリのビルドを開始
        $query = Contact::query();

        // 1. キーワード検索（名前・メールアドレス）
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('last_name', 'LIKE', "%{$keyword}%")
                    ->orWhere('email', 'LIKE', "%{$keyword}%");
            });
        }

        // 2. 性別検索（1:男性, 2:女性, 3:その他 / 0や空は全て対象）
        if (!empty($gender) && $gender !== '0') {
            $query->where('gender', $gender);
        }

        // 3. お問い合わせの種類（カテゴリID）
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // 4. お問い合わせ日付（created_at の日付一致）
        if (!empty($date)) {
            $query->whereDate('created_at', $date);
        }

        // 最新順で取得（検索パラメータを保持してページネーション）
        $contacts = $query->latest()->paginate(7)->withQueryString();

        return view('admin.index', compact('categories', 'tags', 'contacts'));
    }

    /**
     * Display the specified resource.
     */
    public function show(contact $contact)
    {
        return view('admin.show', compact('contact'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        //お問い合わせ削除
        $contact->delete();

        return redirect()->route('admin.index')
            ->with('success', 'お問い合わせを削除しました。');
    }
    public function export(Request $request): StreamedResponse
    {
        // 1. 検索フィルタの適用（indexメソッドと同じ検索ロジックを通す）
        $query = Contact::with('category');

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->keyword}%")
                    ->orWhere('last_name', 'like', "%{$request->keyword}%")
                    ->orWhere('email', 'like', "%{$request->keyword}%");
            });
        }

        if ($request->filled('gender') && !in_array($request->gender, ['0', 'all', ''], true)) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // フィルタ未指定時も含め、基本は新着順で取得
        $contacts = $query->latest()->get();

        // 2. 性別数値 -> 文字列変換マップ
        $genderLabels = [
            1 => '男性',
            2 => '女性',
            3 => 'その他',
        ];

        // 3. CSVダウンロードレスポンス作成
        $response = new StreamedResponse(function () use ($contacts, $genderLabels) {
            $stream = fopen('php://output', 'w');

            // Excel文字化け防止用 UTF-8 BOM の書き込み
            fwrite($stream, "\xEF\xBB\xBF");

            // ヘッダー行を出力
            fputcsv($stream, [
                'ID',
                '氏名',
                '性別',
                'メール',
                '電話',
                '住所',
                '建物',
                'カテゴリ',
                '内容',
                '作成日時',
            ]);

            // データ行を出力
            foreach ($contacts as $contact) {
                fputcsv($stream, [
                    $contact->id,
                    $contact->first_name . ' ' . $contact->last_name,
                    $genderLabels[$contact->gender] ?? '不明',
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category ? $contact->category->content : '',
                    $contact->detail,
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($stream);
        });

        $fileName = 'contacts_' . date('Ymd_His') . '.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
