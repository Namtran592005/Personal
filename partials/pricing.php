    <?php if (count($pricingPlans) > 0): ?>
    <section id="pricing" class="section">
        <div class="deco-rings r1" aria-hidden="true"></div>
        <div class="deco-rings sm r2" aria-hidden="true"></div>
        <div class="deco-rings lg r3" aria-hidden="true"></div>
        <div class="container">
            <div class="section-header fade-in">
                <p class="label">Pricing</p>
                <h2>Bảng giá dịch vụ</h2>
            </div>
            <div class="pricing-grid">
                <?php foreach ($pricingPlans as $plan): ?>
                <div class="pricing-card fade-in<?= $plan['popular'] ? ' popular' : '' ?>">
                    <?php if (!empty($plan['badge'])): ?><span class="badge"><?= h($plan['badge']) ?></span><?php endif; ?>
                    <h4><?= h($plan['title']) ?></h4>
                    <p class="price"><?= h($plan['price']) ?></p>
                    <?php if (!empty($plan['price_note'])): ?><p class="price-note"><?= h($plan['price_note']) ?></p><?php endif; ?>
                    <ul class="pricing-features">
                        <?php
                        $features = array_filter(array_map('trim', explode("\n", $plan['features'] ?? '')));
                        foreach ($features as $feature): ?>
                        <li><i class="ph ph-check"></i> <?= h($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="#contact" class="btn <?= $plan['popular'] ? 'btn-primary' : 'btn-outline' ?>"><?= h($plan['button_text'] ?: 'Bắt đầu ngay') ?></a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
