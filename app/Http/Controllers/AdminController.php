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
    public function index()
    {
        //お問い合わせ一覧とタグ一覧を表示
        $categories = Category::all();
        $tags = Tag::all();
        $contacts = Contact::paginate(10);

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
