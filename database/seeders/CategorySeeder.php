<?php

namespace Database\Seeders;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{

    public function run(): void
    {
        //問い合わせ分類を固定で5件投入する。
        Category::create([
            'content' => '商品のお届けについて',
        ]);
        Category::create([
            'content' => '商品の交換について',
        ]);
        Category::create([
            'content' => '商品トラブル',
        ]);
        Category::create([
            'content' => 'ショップへのお問い合わせ',
        ]);
        Category::create([
            'content' => 'その他',
        ]);
    }
}
