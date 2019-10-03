#!/bin/bash

if [ -z "$1" ]; then
  echo need app id for target
  exit 1
fi
app=$1
release="$(date +%Y%m%d%H%m%s)";

composer install --optimize-autoloader

# zip up the app for uploading
zip -r /tmp/artifact.zip . \
  -x "*\.env*" \
  -x "*\.git*" \
  --quiet

# create the new release directory in the lamp.io app
lio files:new:dir $app releases/$release

# upload the zip into it
lio files:upload /tmp/artifact.zip $app releases/$release/artifact.zip

# unzip it remotely
lio files:update:unarchive $app releases/$release/artifact.zip

# clean up
lio files:delete $app releases/$release/artifact.zip -y

# check if the (shared) storage directory already exists 
if ! lio files:list $app storage -q; then
  # if not, use the one from this release to get started
  lio files:update:move $app releases/$release/storage /storage
fi

# delete this releases storage directory and symlink it to the shared one
lio files:delete $app releases/$release/storage -y -q
lio files:new:symlink $app releases/$release/storage ../../storage

# make sure the app can write to things
lio files:update $app storage --apache_writable=true --recursive
lio files:update $app releases/$release/bootstrap/cache --apache_writable=true --recursive

# upload the local .env.live file to be the remote .env file
lio files:upload .env.live $app releases/$release/.env

# run artisan remotely
lio app_runs:new $app "cd releases/$release && php artisan migrate"

# get the current state of the docroot (public)
public="$(lio files:list $app public -j)"

# if there's a directory already there 
if [ "$(echo $public | jq -r '.data.attributes.is_dir')" = 'true' ]; then
  # back it up as a pre-release 
  lio files:new:dir $app releases/$release-pre
  lio files:update:move $app public releases/$release-pre/public
  # and then symlink public to this new release
  lio files:new:symlink $app public releases/$release/public

# else if there's a symlink already there
elif [ "$(echo $public | jq -r '.data.attributes.is_symlink')" = 'true' ]; then
  # update it to point to this new release
  lio files:update:symlink $app public releases/$release/public
fi

# cleanup the zip
rm -f /tmp/artifact.zip
