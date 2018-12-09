<?php

namespace Roots\WordPressSelfUpdate;

require_once __DIR__ . '/update-wp-releases.php';
require_once __DIR__ . '/build-branch.php';

function getWPReleases()
{
  $wpReleasesPath = getcwd() . '/wp-releases.json';
  if (file_exists($wpReleasesPath)) {
    return json_decode(file_get_contents($wpReleasesPath));
  }
  return collectTags();
}

function tempdir()
{
  $tempfile = tempnam(sys_get_temp_dir(), 'roots-wp-');
  if (file_exists($tempfile)) {
    unlink($tempfile);
  }
  if (!mkdir($tempfile) && !is_dir($tempfile)) {
    throw new \RuntimeException(sprintf('Directory "%s" was not created', $tempfile));
  }
  return $tempfile;
}

function isVersion($string)
{
  return preg_match('/^[0-9.]+$/', $string);
}

function isGithubToken($string) {
  return preg_match('/^[a-z0-9]+$/i', $string);
}

function run($cmd) {
  $code = null;
  system($cmd, $code);
  return $code === 0;
}

function gitTagExists($tag)
{
  if (!isVersion($tag)) {
    throw new \RuntimeException("refusing to pass '$tag' to the shell");
  }
  $ref = escapeshellarg("refs/tags/$tag");
  
  return run("git show-ref --tags --quiet --verify -- $ref");
}

function createGitBranchFromDir($dir, $version)
{
  if (!isVersion($version)) {
    throw new \RuntimeException("refusing to pass '$version' to the shell");
  }
  $safeVersion = escapeshellarg($version);
  $githubToken = getenv('GITHUB_TOKEN');
  if (!isGithubToken($githubToken)) {
    throw new \RuntimeException("refusing to proceed with possibly invalid GITHUB TOKEN $githubToken");
  }
  $remote = escapeshellarg("https://$githubToken@github.com/roots/wordpress.git");
  $branch = escapeshellarg("branch-$version");
  
  $prev = getcwd();
  chdir($dir);
  try {
    if (!run('git init')) {
      throw new \RuntimeException("failed to git init in $dir");
    }
    if (
      !run('git config user.name "Roots Ladybug"') ||
      !run('git config user.email "ben+ladybug@roots.io"')
    ) {
      throw new \RuntimeException("could not set git info for $dir");
    }
    
    if (!run("git checkout -b $branch")) {
      throw new \RuntimeException("failed to git create branch $safeVersion");
    }
    if (
      !run('git add .') ||
      !run("git commit -a -m $safeVersion")
    ) {
      throw new \RuntimeException("failed to commit $safeVersion");
    }
    if (!run("git push $remote $branch")) {
      throw new \RuntimeException("failed to push $safeVersion to $remote");
    }
    if (
      !run("git tag $safeVersion") ||
      !run("git push remote $safeVersion")
    ) {
      throw new \RuntimeException("failed to tag $safeVersion");
    }
  } finally {
    chdir($prev);
  }
  
  return true;
}

function pushTags()
{
  $stagingDir = tempdir();
  foreach (getWPReleases() as $release) {
    $version = $release->name;
    
    if (empty($version)) {
      throw new \RuntimeException('encountered release with no name');
    }
    
    if (!isVersion($version)) {
      throw new \RuntimeException("tag '$version' does not look like a version number");
    }
    
    if (gitTagExists($version)) {
      echo "already have a tag for '$version'\n";
      continue;
    }
    
    $tagDir = "$stagingDir/$version";
    if (!mkdir($tagDir) && !is_dir($tagDir)) {
      throw new \RuntimeException(sprintf('Directory "%s" was not created', $tagDir));
    }
    $built = buildBranch($version, $release->zipball_url, $tagDir);
    if (!$built) {
      throw new \RuntimeException("failed to build out $version");
    }
    $pushed = createGitBranchFromDir($tagDir, $version);
    if (!$pushed) {
      throw new \RuntimeException("failed to push $tagDir to remote");
    }
  }
  
  return true;
}

// if run on cli
if ($argv && $argv[0] && realpath($argv[0]) === __FILE__) {
  $usage = "usage: update-repo.php\n";
  
  $args = array_slice($argv, 1);
  
  if (count($args) === 1 && in_array($args[0], ['-h', '--help'])) {
    echo $usage;
    exit(0);
  }
  
  $result = pushTags();
  fwrite(STDERR, ($result ? 'success!' : 'failure'));
  exit($result ? 0 : 1);
}
