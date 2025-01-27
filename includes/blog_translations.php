<?php

$blog_translations = [
    'bg' => [
        'back_to_posts' => 'Към всички публикации',
        'share' => 'Споделете',
        'related_posts' => 'Подобни публикации',
        'read_more' => 'Прочети повече'
    ],
    'en' => [
        'back_to_posts' => 'Back to all posts',
        'share' => 'Share',
        'related_posts' => 'Related Posts',
        'read_more' => 'Read more'
    ],
    'de' => [
        'back_to_posts' => 'Zurück zu allen Beiträgen',
        'share' => 'Teilen',
        'related_posts' => 'Ähnliche Beiträge',
        'read_more' => 'Weiterlesen'
    ],
    'ru' => [
        'back_to_posts' => 'Ко всем публикациям',
        'share' => 'Поделиться',
        'related_posts' => 'Похожие публикации',
        'read_more' => 'Читать далее'
    ]
];

// Добавяме преводите към глобалния масив с преводи
if (isset($translations)) {
    $translations['blog'] = $blog_translations;
} else {
    $translations = ['blog' => $blog_translations];
} 