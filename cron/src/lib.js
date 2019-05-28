const url = require('url');

const fetch = require('node-fetch');
const semver = require('semver');
const { WebClient } = require('@slack/client');

const last = function (arr) {
  return [].concat(arr).pop()
};

const TRAVIS_REPO = 'roots%2Fwordpress';

async function github(endpoint) {
  const headers = new fetch.Headers();
  headers.append('User-Agent', 'roots-ladybug');
  const token = process.env.GITHUB_TOKEN;
  if (token) {
    headers.append('Authorization', `token ${token}`)
  }
  const requestUrl = url.resolve('https://api.github.com', endpoint);
  const response = await fetch(requestUrl, {headers});

  if ([401, 403].includes(response.status)) {
    const remaining = response.headers.get('X-RateLimit-Remaining');
    let message = `${response.status} ${response.statusText} from github api. Rate limit remaining: '${remaining}'`;
    throw new Error(message);
  }

  return response;
}

async function notifySlack(msg) {
  const token = process.env.SLACK_TOKEN;
  const web = new WebClient(token);

  try {
    const response = await web.chat.postMessage({
      channel: '#bedrock',
      as_user: false,
      username: 'Lambda',
      icon_emoji: ':bedrock:',
      text: msg,
    });

    console.log('Slack message sent', response.ts);
  } catch(error) {
    console.log(error);
  }
}

async function triggerTravisBuild() {
  const body = {
    request: { branch: "src" }
  };

  const response = await fetch(`https://api.travis-ci.com/repo/${TRAVIS_REPO}/requests`, {
    method: 'post',
    body: JSON.stringify(body),
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Travis-API-Version': '3',
      'Authorization': `token ${process.env.TRAVIS_API_KEY}`,
    }
  });

  if (!response.ok) {
    throw new Error('non-200 response from travis api');
  }

  console.log('Travis CI build triggered');
  return await response.json();
}

async function getLatestReleaseInFeed() {
  const response = await github('/repos/wordpress/wordpress/tags');

  if (!response.ok) {
    throw new Error(`could not fetch wordpress/wordpress versions, status code ${response.status}`);
  }

  const releases = await response.json();

  if (!Array.isArray(releases)) {
    throw new TypeError('wordpress/wordpress tags is not an array');
  }

  if (releases.length < 1) {
    throw new Error('empty wordpress/wordpress releases');
  }

  return last(
    releases
      .map(({name}) => ({wp: name, semver: semver.coerce(name)}))
      .sort((a, b) => semver.compare(a.semver.version, b.semver.version))
  ).wp;
}

async function releaseExistsInRepo(tag) {
  const response = await github(`/repos/roots/wordpress/git/refs/tags/${tag}`);

  if (![200, 404].includes(response.status)) {
    throw new Error(
      `got unexpected ${response.status} ${response.statusText} from github api for releaseExistsInRepo`
    );
  }

  return response.ok;
}

module.exports = {
  getLatestReleaseInFeed,
  releaseExistsInRepo,
  notifySlack,
  triggerTravisBuild,
};
