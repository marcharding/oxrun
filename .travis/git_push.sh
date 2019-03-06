#!/usr/bin/env bash

setup_git() {
  git config --global user.email "travis@travis-ci.org"
  git config --global user.name "Travis CI"
  git checkout ${TRAVIS_BRANCH}
}

commit_readme() {
    git add $1
    git commit --message "Updated commands docu in README.md. (#$TRAVIS_BUILD_NUMBER) [ci skip]"
}

upload_files() {
  git remote add oxprojects https://${GITHUB_TOKEN}@github.com/OXIDprojects/oxrun.git > /dev/null 2>&1
  git push --quiet --set-upstream oxprojects ${TRAVIS_BRANCH}
}

cd $(dirname $0);
BASE_DIR=$(pwd -P);
README="$BASE_DIR/../README.md";
cd -;


setup_git
commit_readme "$README"
upload_files
