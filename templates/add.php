<form name="add-lot" class="form form--add-lot container <?= !empty($errors) ? "form--invalid" : "" ?>" action="add.php"
    method="post" enctype="multipart/form-data">
    <h2>Добавление лота</h2>
    <div class="form__container-two">
        <div class="form__item <?= isset($errors['lot-name']) ? "form__item--invalid" : "" ?>">
            <label for="lot-name">Наименование <sup>*</sup></label>
            <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота"
                value="<?= request_get_post_val('lot-name'); ?>">
            <span class="form__error"><?= $errors['lot-name'] ?? ""; ?></span>
        </div>
        <div class="form__item <?= isset($errors['lot-category']) ? "form__item--invalid" : "" ?>">
            <label for="category">Категория <sup>*</sup></label>
            <select id="category" name="lot-category">
                <option>Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?=$category['title']; ?></option>
                <?php endforeach; ?>
            </select>
            <span class="form__error"><?= $errors['lot-category'] ?? ""; ?></span>
        </div>
    </div>
    <div class="form__item form__item--wide <?= isset($errors['lot-description']) ? "form__item--invalid" : "" ?>">
        <label for="lot-description">Описание <sup>*</sup></label>
        <textarea id="lot-description" name="lot-description"
            placeholder="Напишите описание лота"><?= request_get_post_val('lot-description'); ?></textarea>
        <span class="form__error"><?= $errors['lot-description'] ?? ""; ?></span>
    </div>
    <div class="form__item form__item--file <?= isset($errors['lot-img']) ? "form__item--invalid" : "" ?>">
        <label>Изображение <sup>*</sup></label>
        <div class="form__input-file">
            <input class="visually-hidden" type="file" id="lot-img" name="lot-img">
            <label for="lot-img">
                Добавить
            </label>
            <span class="form__error"><?= $errors['lot-img'] ?? ""; ?></span>
        </div>
    </div>
    <div class="form__container-three">
        <div class="form__item form__item--small <?= isset($errors['lot-rate']) ? "form__item--invalid" : "" ?>">
            <label for="lot-rate">Начальная цена <sup>*</sup></label>
            <input id="lot-rate" type="text" name="lot-rate" placeholder="Введите число"
                value="<?= request_get_post_val('lot-rate'); ?>">
            <span class="form__error"><?=$errors['lot-rate'] ?? "";?></span>
        </div>
        <div class="form__item form__item--small <?= isset($errors['lot-step']) ? "form__item--invalid" : "" ?>">
            <label for="lot-step">Шаг ставки <sup>*</sup></label>
            <input id="lot-step" type="text" name="lot-step" placeholder="1 по умолчанию"
                value="<?= request_get_post_val('lot-step'); ?>">
            <span class="form__error"><?=$errors['lot-step'] ?? "";?></span>
        </div>
        <div class="form__item <?= isset($errors['lot-date']) ? "form__item--invalid" : "" ?>">
            <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
            <input class="form__input-date" id="lot-date" type="date" name="lot-date"
                value="<?= request_get_post_val('lot-date'); ?>" placeholder="Введите дату в формате ГГГГ-ММ-ДД">
            <span class="form__error"><?=$errors['lot-date'] ?? "";?></span>
        </div>
    </div>
    <?php if (!empty($errors)): ?>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме</span>
    <?php endif; ?>
    <button type="submit" class="button">Добавить лот</button>
</form>