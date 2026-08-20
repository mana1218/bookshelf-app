<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Book;
use App\Models\User;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $book1 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
        ]);

        $book1->genres()->sync([1]);

        $book2 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
        ]);

        $book2->genres()->sync([2, 4]);

        $book3 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => 'リーダブルコード',
            'author' => 'Dustin Boswell ',
            'isbn' => '9784873115658',
            'published_date' => '2012-06-23',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
        ]);

        $book3->genres()->sync([3]);

        $book4 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => '7つの習慣',
            'author' => 'スティーブン・R・コヴィー',
            'isbn' => '9784863940246',
            'published_date' => '2013-08-30',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
        ]);

        $book4->genres()->sync([2, 4]);

        $book5 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => '坊っちゃん',
            'author' => ' 夏目漱石',
            'isbn' => '9784101010021',
            'published_date' => '1906-04-01',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
        ]);

        $book5->genres()->sync([1]);

        $book6 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => 'サピエンス全史',
            'author' => 'ユヴァル・ノア・ハラリ',
            'isbn' => '9784309226712',
            'published_date' => '2016-09-08',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
        ]);

        $book6->genres()->sync([6, 7]);

        $book7 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'isbn' => '9784048930598',
            'published_date' => '2017-12-18',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
        ]);

        $book7->genres()->sync([3]);

        $book8 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => '嫌われる勇気',
            'author' => '岸見一郎・古賀史健 ',
            'isbn' => '9784478025819',
            'published_date' => '2013-12-13',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
        ]);

        $book8->genres()->sync([4]);

        $book9 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => '火花',
            'author' => '又吉直樹',
            'isbn' => '9784163902302',
            'published_date' => '2015-03-11',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
        ]);

        $book9->genres()->sync([1]);

        $book10 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => 'FACTFULNESS',
            'author' => 'ハンス・ロスリング',
            'isbn' => '9784822289607',
            'published_date' => '2019-01-11',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
        ]);

        $book10->genres()->sync([2, 7]);

        $book11 = Book::firstOrCreate([
            'user_id' => $users->random()->id,
            'title' => 'コンテナ物語',
            'author' => 'マルク・レビンソン',
            'isbn' => '9784822251468',
            'published_date' => '2007-01-18',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
        ]);

        $book11->genres()->sync([2, 6]);
    }
}
