<?php

namespace Roots\WordPressSelfUpdate;

function getLicense()
{
  return <<<EOT
MIT License

Copyright (c) 2018 Roots

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
EOT;
}

function makeComposerPackage($version, $zipURL)
{
  return [
    'name' => 'roots/wordpress',
    'description' => 'WordPress is web software you can use to create a beautiful website or blog.',
    'keywords' => [
      'wordpress',
      'blog',
      'cms'
    ],
    'homepage' => 'http://wordpress.org/',
    'license' => 'GPL-2.0-or-later',
    'authors' => [
      [
        'name' => 'WordPress Community',
        'homepage' => 'http://wordpress.org/about/'
      ]
    ],
    'support' => [
      'issues' => 'http://core.trac.wordpress.org/',
      'forum' => 'http://wordpress.org/support/',
      'wiki' => 'http://codex.wordpress.org/',
      'irc' => 'irc://irc.freenode.net/wordpress',
      'source' => 'http://core.trac.wordpress.org/browser'
    ],
    'type' => 'wordpress-core',
    'version' => $version,
    'require' => [
      'php' => '>=5.3.2',
      'johnpbloch/wordpress-core-installer' => '^1.0'
    ],
    'dist' => [
      'url' => $zipURL,
      'type' => 'zip'
    ]
  ];
}

/**
 * @param array $package
 * @param string $path
 * @return bool|int
 */
function writeComposerJSON($package, $path)
{
  $json_options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;
  return file_put_contents(
    $path,
    json_encode($package, $json_options)
  );
}

function buildBranch($version, $zipURL, $dir)
{
  $licenseWritten = (bool)file_put_contents("${dir}/LICENSE", getLicense());
  
  $composerJSONWritten = (bool)writeComposerJSON(
    makeComposerPackage($version, $zipURL),
    "${dir}/composer.json"
  );
  
  return $licenseWritten && $composerJSONWritten;
}

// if run on cli
if ($argv && $argv[0] && realpath($argv[0]) === __FILE__) {
  $usage = "usage: build-branch.php {{version}} {{zip_url}} {{dir}}\n";
  
  $args = array_slice($argv, 1);
  
  if (count($args) === 1 && in_array($args[0], ['-h', '--help'])) {
    echo $usage;
    exit(0);
  }
  
  if (count($args) !== 3) {
    echo $usage;
    exit(1);
  }
  
  $result = buildBranch(...$args);
  fwrite(STDERR, ($result ? 'success!' : 'failure'));
  exit($result ? 0 : 1);
}
