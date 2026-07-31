    <section id="faq" class="section section-alt">
        <div class="deco-rings r1" aria-hidden="true"></div>
        <div class="deco-rings sm r2" aria-hidden="true"></div>
        <div class="deco-rings lg r3" aria-hidden="true"></div>
        <div class="container">
            <div class="section-header fade-in">
                <p class="label">FAQ</p>
                <h2>Câu hỏi thường gặp</h2>
            </div>
            <div class="faq-list">
                <?php if (isset($faqs) && count($faqs) > 0): ?>
                    <?php foreach ($faqs as $item): ?>
                    <div class="faq-item fade-in">
                        <div class="faq-question"><?= h($item['question']) ?></div>
                        <div class="faq-answer"><?= nl2br(h($item['answer'])) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="faq-item fade-in">
                    <div class="faq-question">What technologies do you work with?</div>
                    <div class="faq-answer">I work across the full stack — from React, Next.js, and TypeScript on the frontend to Node.js, Python, and Go on the backend. I'm always exploring new tools and frameworks to find the best solution for each project.</div>
                </div>
                <div class="faq-item fade-in">
                    <div class="faq-question">Are you available for freelance work?</div>
                    <div class="faq-answer">I'm open to freelance projects and collaborations. Whether it's building a web application, contributing to an open-source project, or providing technical consultation, feel free to reach out.</div>
                </div>
                <div class="faq-item fade-in">
                    <div class="faq-question">What's your preferred development workflow?</div>
                    <div class="faq-answer">I follow an agile approach — breaking projects into small, iterative milestones. I prioritise clean code, thorough testing, and clear communication throughout the development process.</div>
                </div>
                <div class="faq-item fade-in">
                    <div class="faq-question">How can I get in touch with you?</div>
                    <div class="faq-answer">You can reach me via email at <a href="mailto:hello@namtran.dev" style="color:#1d1d1f;text-decoration:underline">hello@namtran.dev</a>. I typically respond within 24 hours.</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
