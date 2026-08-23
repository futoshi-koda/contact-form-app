<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminRequest;
use App\Models\Tag;
use App\Models\Category;
use App\Models\Contact;

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
        $contacts = $query->latest()->paginate(10)->withQueryString();

        return view('admin.index', compact('categories', 'tags', 'contacts'));
    }


    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(contact $contact)
    {
        return view('admin.show', compact('contact'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
}
