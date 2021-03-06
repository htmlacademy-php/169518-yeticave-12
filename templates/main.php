<main class="container">
    <section class="promo">
        <h2 class="promo__title">Нужен стафф для катки?</h2>
        <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и
            горнолыжное снаряжение.</p>
        <ul class="promo__list">
            <?php foreach ($categories as $category): ?>
                <li class="promo__item promo__item--<?= htmlspecialchars($category['symbol']); ?>">
                    <a class="promo__link"
                       href="category.php?name=<?= htmlspecialchars($category['symbol']); ?>"><?= htmlspecialchars($category['title']); ?></a>
                </li>
            <?php endforeach; ?>

        </ul>
    </section>
    <section class="lots">
        <div class="lots__header">
            <h2>Открытые лоты</h2>
        </div>
        <ul class="lots__list">
            <?php foreach ($items as $key => $val): ?>
                <li class="lots__item lot">
                    <div class="lot__image">
                        <img src="<?= htmlspecialchars($val['image']); ?>" width="350" height="260"
                             alt="<?= htmlspecialchars($val['heading']); ?>">
                    </div>
                    <div class="lot__info">
                        <span class="lot__category"><?= htmlspecialchars($val['title']); ?></span>
                        <h3 class="lot__title"><a class="text-link"
                                                  href="lot.php?id=<?= $val['id']; ?>"><?= htmlspecialchars($val['heading']); ?></a>
                        </h3>
                        <div class="lot__state">
                            <div class="lot__rate">
                                <span class="lot__amount"><?= get_bid_text($val['count_bets']); ?></span>
                                <span class="lot__cost"><?= auction_price($val['price']); ?></span>
                            </div>
                            <?php $time_rest = date_finishing(htmlspecialchars($val['finish'])); ?>
                            <div
                                class="lot__timer timer <?= ($time_rest['hours'] === '00') ? 'timer--finishing' : ' ' ?>">
                                <?= $time_rest['hours']; ?> : <?= $time_rest['minutes']; ?>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if ($pages_count > 1): ?>
            <ul class="pagination-list">
                <li class="pagination-item pagination-item-prev"><a
                        href="index.php<?= (CUR_PAGE == 1) ? '' : '?page=' . (CUR_PAGE - 1); ?>">Назад</a></li>
                <?php foreach ($pages as $page): ?>
                    <li class="pagination-item <?php if ($page == CUR_PAGE): ?>pagination-item-active<?php endif; ?>">
                        <a href="index.php?page=<?= $page; ?>"><?= $page; ?></a>
                    </li>
                <?php endforeach; ?>
                <li class="pagination-item pagination-item-next"><a
                        href="index.php<?= (CUR_PAGE == $pages_count) ? '' : '?page=' . (CUR_PAGE + 1); ?>">Вперед</a>
                </li>
            </ul>
        <?php endif; ?>
    </section>

