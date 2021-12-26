<section class="lot-item container">
    <h2><?=htmlspecialchars($single_item['heading']); ?></h2>
    <div class="lot-item__content">
        <div class="lot-item__left">
            <div class="lot-item__image">
                <img src="<?=htmlspecialchars($single_item['image']); ?>" width="730" height="548" alt="Сноуборд">
            </div>
            <p class="lot-item__category">Категория:
                <span><?=htmlspecialchars($single_item['title']); ?></span></p>
            <p class="lot-item__description"><?=htmlspecialchars($single_item['description']); ?></p>
        </div>
        <div class="lot-item__right">
            <div class="lot-item__state">
            <?php if ($single_item['diff'] > 0) : ?>
                <?php $time_rest = date_finishing(htmlspecialchars($single_item['finish'])); ?>
                <div class="lot-item__timer timer <?=($time_rest['hours'] === '00') ? 'timer--finishing' : '' ?>">
                    <?=$time_rest['hours']; ?> : <?=$time_rest['minutes']; ?>
                </div>
                <?php else : ?>
                    <p>Торги окончены</p>
                <?php endif ?>
                <div class="lot-item__cost-state">
                    <div class="lot-item__rate">
                        <span class="lot-item__amount"><?= ($single_item['diff'] > 0)  ? 'Текущая цена' : 'Выигравшая ставка' ?></span>
                        <span class="lot-item__cost"><?=htmlspecialchars($single_item['price']); ?> ₽</span>
                    </div>
                    <div class="lot-item__min-cost" <?= ($single_item['diff'] > 0)  ? '' : 'style="display:none;"' ?>>
                        Мин. ставка <span><?=htmlspecialchars($single_item['min_bet']); ?></span>
                    </div>
                </div>
                <?php if ($user && $single_item['diff'] > 0) : ?>
                    <form class="lot-item__form" action="lot.php?id=<?=htmlspecialchars($single_item['id']); ?>" method="post" autocomplete="off">
                    <?php if (htmlspecialchars($user['id']) === htmlspecialchars($single_item['user_id'])) : ?>
                            <p>Вы не можете делать ставки на свой лот</p>
                            <?php else : ?>
                        <p class="lot-item__form-item form__item <?= isset($errors['new-bet']) ? "form__item--invalid" : "" ?>">
                        
                        <label for="cost">Ваша ставка</label>
                            <input id="new-bet" type="text" name="new-bet" placeholder="<?=htmlspecialchars($single_item['min_bet']); ?>">
                            <span class="form__error"><?= $errors['new-bet'] ?? ""; ?></span>
                        </p>
                        <button type="submit" class="button">Сделать ставку</button>
                        <?php endif ?>
                    </form>
                    <?php endif ?>
            </div>
            <?php if ($user && ($single_item['count_bets'] > 0)): ?>
                <div class="history">
                    <h3>История ставок (<span><?=htmlspecialchars($single_item['count_bets']); ?></span>)</h3>
                    <table class="history__list">
                    <?php foreach ($bets as $key => $val): ?>
                        <?php $bet_time = bet_duration(htmlspecialchars($val['date'])); ?>
                        <tr class="history__item">
                            <td class="history__name"><?=htmlspecialchars($val['username']); ?></td>
                            <td class="history__price"><?=htmlspecialchars($val['price']); ?></td>
                            <td class="history__time">
                            <?= !isset($bet_time) ? htmlspecialchars($val['new_date']) : $bet_time; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif ?>
        </div>
    </div>
</section>
