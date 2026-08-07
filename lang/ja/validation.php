<?php

return [
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で入力してください。',
    'integer' => ':attributeは整数で入力してください。',
    'array' => ':attributeは配列で入力してください。',
    'date' => ':attributeは正しい日付形式で入力してください。',
    'email' => ':attributeはメールアドレス形式で入力してください。',
    'confirmed' => ':attributeが確認用と一致しません。',
    'unique' => 'その:attributeは既に使用されています。',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
        'numeric' => ':attributeは:max以下で指定してください。',
    ],
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください。',
        'numeric' => ':attributeは:min以上で指定してください。',
    ],

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'title' => 'タイトル',
        'author' => '著者',
        'isbn' => 'ISBN',
        'publication' => '出版日',
        'evaluation' => '評価値',
        'comment' => 'コメント',
        'user_id' => 'ユーザー',
        'genre_id' => 'ジャンル',
        'book_id' => '書籍',
        'review_id' => 'レビュー',
        'favorite_id' => 'お気に入り',
        'like_id' => 'いいね',
    ],
];