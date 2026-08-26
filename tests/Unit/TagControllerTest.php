<?php

namespace Tests\Unit;

use App\Http\Requests\TagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Route;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function タグ新規登録にてタグ名の必須入力_文字数制限_一意性が維持される(): void
    {
        // 1. 正常系：有効なタグ名
        $validData = ['name' => '新規タグ'];
        $request = TagRequest::create('/tags', 'POST', $validData);
        $validator = Validator::make($validData, $request->rules());
        $this->assertFalse($validator->fails());

        // 2. 異常系：タグ名未入力
        $emptyData = ['name' => ''];
        $request = TagRequest::create('/tags', 'POST', $emptyData);
        $validator = Validator::make($emptyData, $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());

        // 3. 異常系：タグ名51文字
        $longData = ['name' => str_repeat('あ', 51)];
        $request = TagRequest::create('/tags', 'POST', $longData);
        $validator = Validator::make($longData, $request->rules());
        $this->assertTrue($validator->fails());

        // 4. 異常系：既存タグ名と同名
        $existingTag = Tag::first();
        $duplicateData = ['name' => $existingTag->name];
        $request = TagRequest::create('/tags', 'POST', $duplicateData);
        $validator = Validator::make($duplicateData, $request->rules());
        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function タグ更新にて自身の名前維持は可能だが他で既に使用されているタグ名への変更は拒否(): void
    {
        $tag = Tag::first(); // 対象のタグ

        // 1. 正常系：自身の名前のまま更新は許可
        $sameNameData = ['name' => $tag->name];

        $request = TagRequest::create("/tags/{$tag->id}", 'PUT', $sameNameData);
        $route = (new Route('PUT', '/tags/{tag}', []))->bind($request);
        $route->setParameter('tag', (string) $tag->id);
        $request->setRouteResolver(fn() => $route);

        $validator = Validator::make($sameNameData, $request->rules());
        $this->assertFalse($validator->fails());

        // 2. 異常系：他で既に使用されているタグ名への変更は拒否
        $anotherTag = Tag::skip(1)->first(); // 既存タグ
        if ($anotherTag) {
            $duplicateData = ['name' => $anotherTag->name];

            $requestDuplicate = TagRequest::create("/tags/{$tag->id}", 'PUT', $duplicateData);
            $routeDuplicate = (new Route('PUT', '/tags/{tag}', []))->bind($requestDuplicate);
            $routeDuplicate->setParameter('tag', (string) $tag->id);
            $requestDuplicate->setRouteResolver(fn() => $routeDuplicate);

            $validatorDuplicate = Validator::make($duplicateData, $requestDuplicate->rules());
            $this->assertTrue($validatorDuplicate->fails());
            $this->assertArrayHasKey('name', $validatorDuplicate->errors()->toArray());
        }
    }
}