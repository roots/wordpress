const {
  getLatestReleaseInFeed,
  releaseExistsInRepo,
  notifySlack,
  triggerTravisBuild,
} = require('./lib.js');


async function checkForNewRelease() {
  const tag = await getLatestReleaseInFeed();
  console.log(`Latest wordpress/wordpress release: ${tag}`);
  const existsInRepo = await releaseExistsInRepo(tag);

  if (!existsInRepo) {
    console.log(`Release for ${tag} does not in roots/wordpress`);
    try {
      await triggerTravisBuild();
      notifySlack(`TravisCI build triggered for tag ${tag}`);
    } catch (e) {
      console.error('failed to trigger travis build');
    }
  } else {
    console.log(`Release for ${tag} already exists in roots/wordpress`);
  }
}

checkForNewRelease();
