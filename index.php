<?php
require 'includes/config.php';
require 'includes/functions.php';

// Default profile data (fallback when DB is unavailable)
$profile = [
    'name' => 'Nam Trần',
    'title' => 'Developer & Designer',
    'bio' => '',
    'email' => 'hello@namtran.dev',
    'social_github' => '#',
    'social_linkedin' => '#',
    'social_twitter' => '#',
    'social_dribbble' => '#',
    'social_facebook' => '',
    'social_instagram' => '',
    'social_threads' => '',
    'social_tiktok' => '',
];

if ($dbAvailable) {
    try {
        $stmt = $pdo->query("SELECT * FROM profile WHERE id = 1");
        $profileDb = $stmt->fetch();
        if ($profileDb) $profile = array_merge($profile, $profileDb);
    } catch (PDOException $e) {}
}

// Fetch projects from GitHub
require 'includes/github.php';
$githubUser = $settings['github_username'] ?? 'namtran592005';
$projects = fetchGithubRepos($githubUser, GITHUB_MAX_REPOS, $githubUser);

// Skills from DB (fallback: empty)
$skills = [];
if ($dbAvailable) {
    try {
        $skills = $pdo->query("SELECT * FROM skills WHERE visible = 1 ORDER BY sort_order ASC")->fetchAll();
    } catch (PDOException $e) {
        try { $skills = $pdo->query("SELECT * FROM skills ORDER BY sort_order ASC")->fetchAll(); } catch (PDOException $e2) {}
    }
}

// FAQs from DB
$faqs = [];
if ($dbAvailable) {
    try {
        $faqs = $pdo->query("SELECT * FROM faqs WHERE visible = 1 ORDER BY sort_order ASC, id DESC")->fetchAll();
    } catch (PDOException $e) {
        try { $faqs = $pdo->query("SELECT * FROM faqs ORDER BY sort_order ASC, id DESC")->fetchAll(); } catch (PDOException $e2) {}
    }
}

// Experiences from DB
$experiences = [];
if ($dbAvailable) {
    try {
        $experiences = $pdo->query("SELECT * FROM experiences WHERE visible = 1 ORDER BY sort_order ASC, id DESC")->fetchAll();
    } catch (PDOException $e) {
        try { $experiences = $pdo->query("SELECT * FROM experiences ORDER BY sort_order ASC, id DESC")->fetchAll(); } catch (PDOException $e2) {}
    }
}

// Pricing plans from DB
$pricingPlans = [];
if ($dbAvailable) {
    try {
        $pricingPlans = $pdo->query("SELECT * FROM pricing_plans WHERE visible = 1 ORDER BY sort_order ASC, id ASC")->fetchAll();
    } catch (PDOException $e) {
        try { $pricingPlans = $pdo->query("SELECT * FROM pricing_plans ORDER BY sort_order ASC, id ASC")->fetchAll(); } catch (PDOException $e2) {}
    }
}

$pageTitle = $profile['name'] . t('site_title');

include 'partials/header.php';
include 'partials/nav.php';
include 'partials/hero.php';
if (($settings['show_skills'] ?? '1') === '1') include 'partials/skills.php';
if (($settings['show_experience'] ?? '1') === '1') include 'partials/experiences.php';
if (($settings['show_projects'] ?? '1') === '1') include 'partials/projects.php';
if (($settings['show_faq'] ?? '1') === '1') include 'partials/faq.php';
if (($settings['show_pricing'] ?? '1') === '1') include 'partials/pricing.php';
if (($settings['show_contact'] ?? '1') === '1') include 'partials/contact.php';
include 'partials/footer.php';
