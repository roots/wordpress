const url = require('url');

const fetch = require('node-fetch');
const semver = require('semver');
const { WebClient } = require('@slack/client');

const last = function (arr) {
  return [].concat(arr).pop()
};

async function github(endpoint, options = {}) {
  const headers = new fetch.Headers();
  headers.append('User-Agent', 'roots-ladybug');
  headers.append('Accept', 'application/vnd.github.v3+json');
  const token = process.env.GITHUB_TOKEN;
  if (token) {
    headers.append('Authorization', `token ${token}`)
  }
  const requestUrl = url.resolve('https://api.github.com', endpoint);
  const response = await fetch(requestUrl, {headers, ...options});

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
      username: 'GitHub',
      icon_emoji: ':bedrock:',
      text: msg,
    });

    console.log('Slack message sent', response.ts);
  } catch(error) {
    console.log(error);
  }
}

async function sendRepoDispatchEvent(tag) {
  const body = {
    event_type: 'wordpress-release',
    client_payload: { version: tag }
  };

  const response = await github('/repos/roots/wordpress/dispatches', {body: JSON.stringify(body), method: 'POST'});

  if (!response.ok) {
    throw new Error(`could not send dispatch event, status code ${response.status}`);
  }
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
  sendRepoDispatchEvent,
};
