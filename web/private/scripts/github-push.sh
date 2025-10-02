#!/bin/bash
set -e

##
# Script to push Pantheon code to GitHub.
# Uses a GitHub token stored in Pantheon private files.
#
# @see .pantheon.yml
# @see https://docs.pantheon.io/guides/quicksilver/hooks
##

echo "== Starting Quicksilver GitHub Push Script =="

cd "$HOME/code"

TOKEN=$(cat $HOME/files/private/prod-workflow-ict-github-token.txt)
echo "Token preview: ${TOKEN:0:10}..."

GITHUB_URL="https://${TOKEN}@github.com/Intercity-Transit/intercity-transit-www.git"

if ! git remote | grep -q transitgithub; then
  echo "Adding remote"
  git remote add transitgithub "$GITHUB_URL"
else
  echo "Updating remote to use latest token"
  git remote set-url transitgithub "$GITHUB_URL"
fi

echo "Pushing Pantheon 'master' branch to GitHub 'master' branch"
git push transitgithub master:master --force 2>&1

echo "== Done =="
