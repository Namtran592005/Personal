<?php
require_once __DIR__ . '/schema.php';

// Cache file is keyed by username so switching GitHub accounts never shows
// stale repos from a previous user, and a reset always triggers a fresh fetch.
function githubCacheFile(string $username): string {
    $cacheDir = __DIR__ . '/../cache';
    if (!is_dir($cacheDir)) @mkdir($cacheDir, 0775, true);
    return $cacheDir . '/github_repos_' . md5(strtolower(trim($username))) . '.json';
}

// Remove every GitHub cache file (all users + legacy name). Returns count removed.
function clearGithubCache(): int {
    $removed = 0;
    foreach (glob(__DIR__ . '/../cache/github_repos*.json') ?: [] as $f) {
        if (@unlink($f)) $removed++;
    }
    return $removed;
}

function fetchGithubRepos(string $username, int $max = GITHUB_MAX_REPOS, string $exclude = ''): array {
    $cacheFile = githubCacheFile($username);

    if (file_exists($cacheFile) && (time() - @filemtime($cacheFile)) < GITHUB_CACHE_TTL) {
        $data = @file_get_contents($cacheFile);
        if ($data !== false) return json_decode($data, true) ?: [];
    }

    $url = "https://api.github.com/users/" . urlencode($username) . "/repos?sort=updated&per_page={$max}&type=public";
    $context = stream_context_create(['http' => [
        'header' => "User-Agent: Personal-Website\r\n",
        'timeout' => 10,
    ]]);

    $response = @file_get_contents($url, false, $context);
    $repos = is_string($response) ? json_decode($response, true) : null;
    if (!is_array($repos) || isset($repos['message'])) {
        // GitHub unreachable or erroring (network / rate limit): serve the last
        // known cache so the projects section never goes blank. It will be
        // refreshed once the API is reachable again.
        $old = @file_get_contents($cacheFile);
        if ($old !== false) {
            $cached = json_decode($old, true);
            if (is_array($cached)) return $cached;
        }
        return [];
    }

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
