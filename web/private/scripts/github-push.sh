#!/bin/bash
set -e

##
# Script to push Pantheon code to GitHub.
# Uses a GitHub token stored in Pantheon private files.
#
# @see .pantheon.yml
# @see https://docs.pantheon.io/guides/quicksilver/hooks
##

# Change into the Pantheon code directory
cd "$HOME/code"

# Load the GitHub token from Pantheon private files dir
TOKEN=$(cat $HOME/files/private/prod-workflow-ict-github-token.txt)

# Repo URL with HTTPS + token auth
GITHUB_URL="https://${TOKEN}@github.com/Intercity-Transit/intercity-transit-www.git"

# Add "transitgithub" remote if missing
if ! git remote | grep -q transitgithub; then
  git remote add transitgithub "$GITHUB_URL"
else
  # Ensure the URL is updated in case the token changes
  git remote set-url transitgithub "$GITHUB_URL"
fi

# Push Pantheon "master" branch to GitHub "main"
# git push transitgithub master:main --force

# Push Pantheon "master" branch to GitHub "master"
git push transitgithub master:master --force
