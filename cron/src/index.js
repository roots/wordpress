const {
  getLatestReleaseInFeed,
  releaseExistsInRepo,
  notifySlack,
  sendRepoDispatchEvent,
} = require('./lib.js');


async function checkForNewRelease() {
  const tag = await getLatestReleaseInFeed();
  console.log(`Latest wordpress/wordpress release: ${tag}`);
  const existsInRepo = await releaseExistsInRepo(tag);

  if (!existsInRepo) {
    console.log(`Release for ${tag} does not in roots/wordpress`);
    try {
      await sendRepoDispatchEvent(tag);
      notifySlack(`Build triggered for tag ${tag}`);
    } catch (e) {
      console.error('failed to send repo dispatch event');
    }
  } else {
    console.log(`Release for ${tag} already exists in roots/wordpress`);
  }
}

checkForNewRelease();
