<?php

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

require __DIR__ . '/vendor/autoload.php';

$repo = trim($argv[1] ?? '');
$out = $argv[2] ?? ".";
$apiKey = $argv[3] ?? getenv("GITHUB_ACCESS_TOKEN");
if (empty($apiKey)) {
    $apiKey = `composer config -g github-oauth.github.com`;
}
if (empty($repo)) {
    die('
    1st argument must be a valid repository {owner}/{repo}
    ');
}

$client = new Client();
$options = [
    RequestOptions::ALLOW_REDIRECTS => true,
    RequestOptions::HEADERS         => [
        'Accept'        => 'application/vnd.github.v3+json',
        'Authorization' => "token $apiKey",
    ],
];
$response = $client->get("https://api.github.com/repos/{$repo}/releases/latest", $options);

$json = $response->getBody()->getContents();
$release = json_decode($json);
$asset = $release->assets[0];
// download and save
$client->get($asset->url, array_replace_recursive($options, [
    RequestOptions::HEADERS => ['Accept' => 'application/octet-stream'],
    RequestOptions::SINK    => "${out}/{$asset->name}",
]));
