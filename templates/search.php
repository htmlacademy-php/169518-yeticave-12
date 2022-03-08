<section class="lots container">
    <h2>Результаты поиска по запросу «<span><?= $search; ?></span>»</h2>
    <ul class="lots__list">
        <?php foreach ($search_result as $key => $val): ?>
            <li class="lots__item lot">
                <div class="lot__image">
                    <img src="<?= $val['image']; ?>" width="350" height="260" alt="">
                </div>
                <div class="lot__info">
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
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php if ($pages_count > 1): ?>
        <ul class="pagination-list">
            <li class="pagination-item pagination-item-prev"><a href="<?= backward($search_url); ?>">Назад</a></li>
            <?php foreach ($pages as $page): ?>
                <li class="pagination-item <?php if ($page == CUR_PAGE): ?>pagination-item-active<?php endif; ?>">
                    <a href="<?= $search_url; ?>&page=<?= $page; ?>"><?= $page; ?></a>
                </li>
            <?php endforeach; ?>
            <li class="pagination-item pagination-item-next"><a
                    href="<?= forward($search_url, $pages_count); ?>">Вперед</a></li>
        </ul>
    <?php endif; ?>
</section>
