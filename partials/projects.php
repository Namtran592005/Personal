    <?php if (count($projects) > 0):
    $initial = 6;
    $total = count($projects);
    ?>
    <section id="projects" class="section">
        <div class="deco-rings r1" aria-hidden="true"></div>
        <div class="deco-rings sm r2" aria-hidden="true"></div>
        <div class="deco-rings lg r3" aria-hidden="true"></div>
        <div class="container">
            <div class="section-header fade-in">
                <p class="label"><?= t('sec_projects_label') ?></p>
                <h2><?= t('sec_projects_title') ?></h2>
            </div>
            <div class="shuffle-toggle<?= (($settings['shuffle_on_mobile'] ?? '1') === '1') ? ' on-mobile' : '' ?>">
                <label class="shuffle-switch">
                    <input type="checkbox" id="shuffleCheck" checked>
                    <span class="shuffle-slider"></span>
                    <span class="shuffle-label"><?= t('shuffle_effect') ?></span>
                </label>
            </div>
            <div class="projects-grid" id="projects-grid">
                <?php foreach ($projects as $i => $project): ?>
                <div class="project-card fade-in<?= $i >= $initial ? ' hidden' : '' ?>">
                    <div class="project-body">
                        <div class="project-head">
                            <h4><?= h($project['title']) ?></h4>
                            <?php if ($project['stars'] > 0): ?>
                            <span class="project-stars"><i class="ph ph-star"></i> <?= (int)$project['stars'] ?></span>
                            <?php endif; ?>
                        </div>
                        <p><?= h($project['description']) ?></p>
                        <?php if (!empty($project['tech_stack'])): ?>
                        <div class="project-tech">
                            <?php foreach (explode(',', $project['tech_stack']) as $tech): ?>
                            <span><?= h(trim($tech)) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($project['github_url'])): ?>
                        <a href="<?= h($project['github_url']) ?>" class="project-github" target="_blank" rel="noopener">
                            <i class="ph ph-github-logo"></i> <?= t('view_on_github') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if ($total > $initial): ?>
            <div class="show-more-wrap">
                <button id="show-more-btn" class="btn btn-out"><?= t('show_more') ?> (<?= $total - $initial ?>)</button>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>
