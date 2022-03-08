<form name="new-user" class="form container <?= !empty($errors) ? "form--invalid" : "" ?>" action="signin.php"
      method="post" autocomplete="off">
    <h2>Вход</h2>
    <div class="form__item <?= isset($errors['login-email']) ? "form__item--invalid" : "" ?>">
        <label for="login-email">E-mail <sup>*</sup></label>
        <input id="login-email" type="text" name="login-email" placeholder="Введите e-mail"
               value="<?= request_get_post_val('login-email'); ?>">
        <span class="form__error"><?= $errors['login-email'] ?? ""; ?></span>
    </div>
    <div class="form__item form__item--last <?= isset($errors['login-password']) ? "form__item--invalid" : "" ?>">
        <label for="login-password">Пароль <sup>*</sup></label>
        <input id="login-password" type="password" name="login-password" placeholder="Введите пароль"
               value="<?= request_get_post_val('login-password'); ?>">
        <span class="form__error"><?= $errors['login-password'] ?? ""; ?></span>
    </div>
    <button type="submit" class="button">Войти</button>
</form>
