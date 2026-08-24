<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ContactRequest;
use App\Models\Tag;
use App\Models\Category;
use App\Models\Contact;

class ContactController extends Controller
{

    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();
        return view('contact.index', compact('categories', 'tags'));
    }

    public function confirm(ContactRequest $request)
    {
        $validated = $request->validated();
        $request->flash();


        $tagIds = $request->input('tag_ids', []);
        $validated['tag_ids'] = $tagIds;
        $tags = !empty($tagIds) ? Tag::whereIn('id', $tagIds)->get() : collect();

        $category = Category::find($request->category_id);

        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }
    public function store(Request $request)
    {
        $contactData = $request->only([
            'category_id',
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'building',
            'detail',
        ]);

        $contact = Contact::create($contactData);

        if ($request->has('tag_ids')) {
            $contact->tags()->sync($request->input('tag_ids'));
        }

        return view('contact.thanks');
    }
}

