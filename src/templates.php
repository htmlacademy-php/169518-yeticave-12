<?php

function templates_include_layout(bool $is_auth, string $user_name, array $categories): array {
    return [
        'header' => include_template('header.php', [
            'title' => 'YetiCave',
            'is_auth' => $is_auth,
            'user_name' => $user_name
        ]),
        'top_menu' => include_template('top-menu.php', [
            'categories' => $categories
        ])
    ];
}