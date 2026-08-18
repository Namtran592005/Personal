    <?php if (isset($faqs) && count($faqs) > 0): ?>
    <section id="faq" class="section section-alt">
        <div class="deco-rings r1" aria-hidden="true"></div>
        <div class="deco-rings sm r2" aria-hidden="true"></div>
        <div class="deco-rings lg r3" aria-hidden="true"></div>
        <div class="deco-q" aria-hidden="true">
            <svg viewBox="0 0 100 100" preserveAspectRatio="xMidYMid meet">
                <path fill="none" stroke="#ffc94d" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" d="M38 36 C38 22 62 22 62 36 C62 47 50 48 50 58 L50 66"/>
                <circle cx="50" cy="76" r="3.5" fill="none" stroke="#ffc94d" stroke-width="1"/>
            </svg>
        </div>
        <div class="container">
            <div class="section-header fade-in">
                <p class="label"><?= t('sec_faq_label') ?></p>
                <h2><?= t('sec_faq_title') ?></h2>
            </div>
            <div class="faq-list">
                <?php foreach ($faqs as $item): ?>
                <div class="faq-item fade-in">
                    <div class="faq-question"><?= h($item['question']) ?></div>
                    <div class="faq-answer"><?= nl2br(h($item['answer'])) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
