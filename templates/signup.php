<form name="new-user" class="form container <?= !empty($errors) ? "form--invalid" : "" ?>" action="signup.php"
    method="post" autocomplete="off">
    <h2>Регистрация нового аккаунта</h2>
    <div class="form__item <?= isset($errors['new-user-email']) ? "form__item--invalid" : "" ?>">
        <label for="email">E-mail <sup>*</sup></label>
        <input id="email" type="text" name="new-user-email" placeholder="Введите e-mail"
            value="<?= request_get_post_val('new-user-email'); ?>">
        <span class="form__error"><?=$errors['new-user-email'] ?? "";?></span>
    </div>
    <div class="form__item <?= isset($errors['new-user-password']) ? "form__item--invalid" : "" ?>">
        <label for="password">Пароль <sup>*</sup></label>
        <input id="password" type="password" name="new-user-password" placeholder="Введите пароль"
            value="<?= request_get_post_val('new-user-password'); ?>">
        <span class="form__error"><?=$errors['new-user-password'] ?? "";?></span>
    </div>
    <div class="form__item <?= isset($errors['new-user-name']) ? "form__item--invalid" : "" ?>">
        <label for="name">Имя <sup>*</sup></label>
        <input id="name" type="text" name="new-user-name" placeholder="Введите имя"
            value="<?= request_get_post_val('new-user-name'); ?>">
        <span class="form__error"><?=$errors['new-user-name'] ?? "";?></span>
    </div>
    <div class="form__item <?= isset($errors['new-user-contact']) ? "form__item--invalid" : "" ?>">
        <label for="message">Контактные данные <sup>*</sup></label>
        <textarea id="message" name="new-user-contact" placeholder="Напишите как с вами связаться"
            value="<?= request_get_post_val('new-user-contact'); ?>"></textarea>
        <span class="form__error"><?=$errors['new-user-contact'] ?? "";?></span>
    </div>
    <?php if (!empty($errors)): ?>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме</span>
    <?php endif; ?>
    <button type="submit" class="button">Зарегистрироваться</button>
    <a class="text-link" href="signin.php">Уже есть аккаунт</a>
</form>
