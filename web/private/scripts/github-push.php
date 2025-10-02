<?php

/**
 * @file
 * Push Pantheon code to GitHub.
 *
 * Quicksilver wrapper: runs Bash inside PHP so Pantheon shows output.
 */

$bash = <<<BASH
#!/bin/bash
set -e

echo "== Starting Quicksilver GitHub Push Script =="

cd "\$HOME/code"

TOKEN=\$(cat \$HOME/files/private/prod-workflow-ict-github-token.txt)
echo "Token preview: \${TOKEN:0:10}..."

GITHUB_URL="https://\${TOKEN}@github.com/Intercity-Transit/intercity-transit-www.git"

if ! git remote | grep -q transitgithub; then
  echo "Adding remote"
  git remote add transitgithub "\$GITHUB_URL"
else
  echo "Updating remote to use latest token"
  git remote set-url transitgithub "\$GITHUB_URL"
fi

echo "Pushing Pantheon 'master' branch to GitHub 'master' branch"
git push transitgithub master:master --force 2>&1

echo "== Done =="
BASH;

// Run bash and capture all output (stdout + stderr)
$output = shell_exec("bash -s 2>&1 <<'EOS'\n$bash\nEOS");

// Print so it appears in the Pantheon Workflow log.
print $output;
