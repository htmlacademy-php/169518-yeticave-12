<?php

function templates_include_layout(array $user, array $categories) {
    return [
        'header' => include_template('header.php', [
            'title' => 'YetiCave',
            'user' => $user
        ]),
        'top_menu' => include_template('top-menu.php', [
            'categories' => $categories
        ])
    ];
}