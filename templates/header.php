<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title><?= $title; ?></title>
    <link href="css/normalize.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
<div class="page-wrapper">

    <header class="main-header">
        <div class="main-header__container container">
            <h1 class="visually-hidden">YetiCave</h1>
            <a href="index.php" class="main-header__logo">
                <img src="img/logo.svg" width="160" height="39" alt="Логотип компании YetiCave">
            </a>
            <form class="main-header__search" method="get" action="search.php">
                <input type="search" name="search"
                       placeholder="Поиск лота" <?php if (isset($_GET['search'])): ?> value="<?= htmlspecialchars($_GET['search']); ?>"<?php endif; ?>>
                <input class="main-header__search-btn" type="submit" value="Найти">
            </form>
            <a class="main-header__add-lot button" href="add.php">Добавить лот</a>
            <nav class="user-menu">
                <?php if (!$user): ?>
                    <ul class="user-menu__list">
                        <li class="user-menu__item">
                            <a href="signup.php">Регистрация</a>
                        </li>
                        <li class="user-menu__item">
                            <a href="signin.php">Вход</a>
                        </li>
                    </ul>
                <?php else: ?>
                    <div class="user-menu__logged">
                        <p><?= $user['username']; ?></p>
                        <a class="user-menu__bets" href="my-bets.php">Мои ставки</a>
                        <a class="user-menu__logout" href="logout.php">Выход</a>
                    </div>
                <?php endif ?>
            </nav>
        </div>
    </header>
