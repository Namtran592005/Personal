    <?php if (count($skills) > 0): ?>
    <section id="skills" class="section section-alt">
        <div class="deco-rings r1" aria-hidden="true"></div>
        <div class="deco-rings sm r2" aria-hidden="true"></div>
        <div class="deco-rings lg r3" aria-hidden="true"></div>
        <div class="container">
            <div class="section-header fade-in">
                <p class="label">Skills</p>
                <h2>What I Do</h2>
            </div>
            <div class="skills-grid">
                <?php foreach ($skills as $skill): ?>
                <div class="skill-card fade-in">
                    <i class="ph <?= h($skill['icon']) ?>"></i>
                    <h4><?= h($skill['category']) ?></h4>
                    <p><?= h($skill['description']) ?></p>
                    <?php if (!empty($skill['tags'])): ?>
                    <div class="skill-tags">
                        <?php foreach (explode(',', $skill['tags']) as $tag): ?>
                        <span class="skill-tag"><?= h(trim($tag)) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
