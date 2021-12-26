<section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
        <?php foreach ($user_bets as $key => $val): ?>
        <?php $time_rest = date_finishing(htmlspecialchars($val['finish'])); ?>
        <?php $bet_time = bet_duration(htmlspecialchars($val['date'])); ?>
        <?php $class = show_classes($val, $winner); ?>        
        <tr class="rates__item <?=$class['item-end']; ?>">
            <td class="rates__info">
                <div class="rates__img">
                    <img src="<?=htmlspecialchars($val['image']); ?>" width="54" height="40"
                        alt="<?=htmlspecialchars($val['heading']); ?>">
                </div>
                <div>
                    <h3 class="rates__title"><a
                            href="lot.php?id=<?=$val['id']; ?>"><?=htmlspecialchars($val['heading']); ?></a></h3>
                    <p><?= (isset($class['timer-end']) && $class['timer-end'] === 'timer--win') ? htmlspecialchars($val['contact']) : '' ?></p>
                </div>
            </td>
            <td class="rates__category">
                <?=htmlspecialchars($val['title']);?>
            </td>
            <td class="rates__timer">
                <?php if ($val['diff'] > 0) : ?>
                <div class="timer <?= ($time_rest['hours'] === '00') ? 'timer--finishing' : ' ' ?>">
                    <?=$time_rest['hours']; ?> : <?= $time_rest['minutes']; ?>
                </div>
                <?php else: ?>
                <div class="timer <?=$class['timer-end']; ?>"><?= ($class['timer-end'] === 'timer--end') ? 'Торги окончены' : 'Ставка выиграла' ?></div>
                <?php endif; ?>
            </td>
            <td class="rates__price">
                <?=htmlspecialchars($val['price']);?>
            </td>
            <td class="rates__time">
            <?= !isset($bet_time) ? htmlspecialchars($val['new_date']) : $bet_time; ?> 
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>