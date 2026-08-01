<?php
require_once __DIR__ . '/schema.php';

function fetchGithubRepos(string $username, int $max = GITHUB_MAX_REPOS, string $exclude = ''): array {
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
    $cacheFile = $cacheDir . '/github_repos.json';

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < GITHUB_CACHE_TTL) {
        $data = @file_get_contents($cacheFile);
        if ($data !== false) return json_decode($data, true) ?: [];
    }

    $url = "https://api.github.com/users/" . urlencode($username) . "/repos?sort=updated&per_page={$max}&type=public";
    $context = stream_context_create(['http' => [
        'header' => "User-Agent: Personal-Website\r\n",
        'timeout' => 10,
    ]]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        $old = @file_get_contents($cacheFile);
        return $old ? (json_decode($old, true) ?: []) : [];
    }

    $repos = json_decode($response, true);
    if (!is_array($repos) || isset($repos['message'])) return [];

    $projects = [];
    foreach ($repos as $repo) {
        if ($repo['fork']) continue;
        if ($exclude && strtolower($repo['name']) === strtolower($exclude)) continue;

        $techStack = $repo['language'] ?: '';
        if (!empty($repo['topics'])) {
            $topics = array_slice($repo['topics'], 0, 5);
            if ($techStack) array_unshift($topics, $techStack);
            $techStack = implode(', ', $topics);
        }

        $projects[] = [
            'title' => $repo['name'],
            'description' => $repo['description'] ?? 'No description provided.',
            'tech_stack' => $techStack,
            'github_url' => $repo['html_url'],
            'live_url' => $repo['homepage'] ?? '',
            'image' => '',
            'stars' => $repo['stargazers_count'] ?? 0,
        ];
    }

    @file_put_contents($cacheFile, json_encode($projects));
    return $projects;
}
