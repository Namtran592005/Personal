    <?php if (isset($experiences) && count($experiences) > 0): ?>
    <section id="experience" class="section">
        <div class="deco-rings r1" aria-hidden="true"></div>
        <div class="deco-rings sm r2" aria-hidden="true"></div>
        <div class="deco-rings lg r3" aria-hidden="true"></div>
        <div class="container">
            <div class="section-header fade-in">
                <p class="label"><?= t('sec_exp_label') ?></p>
                <h2><?= t('sec_exp_title') ?></h2>
            </div>
            <div class="timeline">
                <?php foreach ($experiences as $exp): ?>
                <div class="timeline-item fade-in">
                    <div class="timeline-dot"></div>
                    <div class="timeline-date"><?= h($exp['start_date'] ?: '?') ?> — <?= h($exp['end_date'] ?: t('present')) ?></div>
                    <h4><?= h($exp['title']) ?></h4>
                    <div class="company"><?= h($exp['company']) ?><?= !empty($exp['location']) ? ' · ' . h($exp['location']) : '' ?></div>
                    <?php if (!empty($exp['description'])): ?>
                    <p><?= nl2br(h($exp['description'])) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
