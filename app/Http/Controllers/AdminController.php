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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.tags.edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        //
        return view('admin.tags.edit', compact('tag'));
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
    public function destroy(string $id)
    {
        //
    }
}
