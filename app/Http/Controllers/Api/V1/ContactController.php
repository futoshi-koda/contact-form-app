<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\StoreContactRequest;
use App\Http\Requests\Api\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    /**
     * AP01: お問い合わせ一覧（検索・ページネーション付き）
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Contact::query();

        // キーワード検索（名前・メール・詳細）
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('detail', 'like', "%{$keyword}%");
            });
        }

        // 性別検索
        if ($request->filled('gender') && $request->input('gender') != 0) {
            $query->where('gender', $request->input('gender'));
        }

        // カテゴリ検索
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // 日付検索
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        // ページネーション件数（デフォルトは 7 件または仕様に合わせる）
        $perPage = $request->input('per_page', 7);

        $contacts = $query->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    /**
     * AP02: お問い合わせ詳細
     */
    public function show(Contact $contact): ContactResource
    {
        return new ContactResource($contact);
    }

    /**
     * AP03: お問い合わせ新規登録
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = Contact::create($request->validated());

        // タグの紐付け（tag_ids が送信されている場合）
        if ($request->has('tag_ids')) {
            $contact->tags()->sync($request->input('tag_ids'));
        }

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * AP04: お問い合わせ更新
     */
    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $contact->update($request->validated());

        // タグの同期
        if ($request->has('tag_ids')) {
            $contact->tags()->sync($request->input('tag_ids'));
        }

        return new ContactResource($contact);
    }

    /**
     * AP05: お問い合わせ削除
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json([
            'message' => 'お問い合わせを削除しました。',
        ], 200);
    }
}
