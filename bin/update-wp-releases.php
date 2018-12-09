<?php

namespace Roots\WordPressSelfUpdate;

/**
 * @param string $url
 * @param array $params
 * @return mixed
 */
function apiGET($url, $params = [])
{
  $curl = curl_init();
  $searchString = http_build_query($params);
  
  $url = "${url}?${searchString}";
  
  // Optional Authentication:
  $githubUsername = getenv('GITHUB_USERNAME');
  $githubToken = getenv('GITHUB_TOKEN');
  if ($githubUsername && $githubToken) {
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "${githubUsername}:${githubToken}");
  }
  curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'User-Agent: Roots-WordPress-Satis',
  ));
  
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  
  try {
    $result = curl_exec($curl);
    
    // Check HTTP status code
    if (!curl_errno($curl)) {
      $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      if ($http_code !== 200) {
        throw new \RuntimeException("api returned code '$http_code' with $result");
      }
    }
  } finally {
    curl_close($curl);
  }
  
  return json_decode($result);
}

function getTags($page = 1)
{
  return apiGET(
    'https://api.github.com/repos/wordpress/wordpress/tags',
    ['page' => $page, 'per_page' => 100]
  );
}

function collectTags()
{
  $tags = [];
  $page = 1;
  do {
    $pageResults = getTags($page);
    if (count($pageResults)) {
      array_push($tags, ...$pageResults);
    }
    ++$page;
  } while (count($pageResults) > 0);
  
  return array_filter($tags, function ($tag) {
    return version_compare($tag->name, '4.0', '>=');
  });
}

function writeReleaseFile($tags)
{
  $json_options = JSON_PRETTY_PRINT;
  return fwrite(
    STDOUT,
    json_encode($tags, $json_options)
  );
}

// if run on cli
if ($argv && $argv[0] && realpath($argv[0]) === __FILE__) {
  $usage = "usage: update-wp-releases.php\n";
  
  $args = array_slice($argv, 1);
  
  if (in_array($args[0], ['-h', '--help'])) {
    echo $usage;
    exit(0);
  }
  
  writeReleaseFile(collectTags());
}
