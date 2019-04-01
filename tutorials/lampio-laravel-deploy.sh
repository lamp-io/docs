#!/bin/bash

set -xe

# usage: 
# cd <your_laravel_app>
# ./lampio-laravel.deploy.sh <lampio app>

LAMPIO_APP=$1
LAMPIO_API="https://api.lamp.io"
LAMPIO_TOK=$(<~/.config/lamp.io/token)
AUTH_HEAD="authorization: Bearer $LAMPIO_TOK"

# wipe the slate
RM_RUN_ID="$(curl -X POST "${LAMPIO_API}/app_runs" \
	-H "accept: application/vnd.api+json" \
	-H "${AUTH_HEAD}" \
	-H "Content-Type: application/vnd.api+json" \
	-d "{\"data\":{\"attributes\":{\"app_id\":\"${LAMPIO_APP}\",\"command\":\"rm -rf *\"},\"type\":\"app_runs\"}}" \
	-sS \
	| jq -r '.data.id' \
)"

# zip up the app
zip -r /tmp/app.zip . -x "*\.env*" -x "*\.git*" -q

until [ "$(curl $LAMPIO_API/app_runs/$RM_RUN_ID -H "$AUTH_HEAD" -sS | jq -r '.data.attributes.complete')" = "true" ]; do sleep 1; done

# upload the zip 
curl $LAMPIO_API/apps/$LAMPIO_APP/files \
        -F "app.zip=@/tmp/app.zip" \
        -H "$AUTH_HEAD" \
	-sSo /dev/null

# extract it
curl $LAMPIO_API/apps/$LAMPIO_APP/files/app.zip \
	-X PATCH \
	-H 'accept: application/vnd.api+json' \
	-H "Content-Type: application/vnd.api+json" \
	-H "$AUTH_HEAD" \
	-d "{\"data\":{\"type\":\"files\",\"id\":\"app.zip\",\"attributes\":{\"command\":\"unarchive\"}}}"  \
	-sSo /dev/null

# delete the zip
curl $LAMPIO_API/apps/$LAMPIO_APP/files/app.zip \
	-X DELETE \
	-H "$AUTH_HEAD" \
	-H 'accept: application/vnd.api+json' \
	-sSo /dev/null

# php artisan migrate
PAM_RUN_ID="$(curl -X POST "${LAMPIO_API}/app_runs" \
        -H "accept: application/vnd.api+json" \
        -H "${AUTH_HEAD}" \
        -H "Content-Type: application/vnd.api+json" \
        -d "{ \"data\": { \"attributes\": { \"app_id\": \"${LAMPIO_APP}\", \"command\": \"php artisan migrate\" }, \"type\": \"app_runs\" }}" \
	-sS \
	| jq -r '.data.id' \
)"

# chown 1
CH1_RUN_ID="$(curl -X POST "${LAMPIO_API}/app_runs" \
        -H "accept: application/vnd.api+json" \
        -H "${AUTH_HEAD}" \
        -H "Content-Type: application/vnd.api+json" \
        -d "{ \"data\": { \"attributes\": { \"app_id\": \"${LAMPIO_APP}\", \"command\": \"chown -R www-data:www-data bootstrap/cache\" }, \"type\": \"app_runs\" }}" \
	-sS \
	| jq -r '.data.id' \
)"

# chown 2
CH2_RUN_ID="$(curl -X POST "${LAMPIO_API}/app_runs" \
        -H "accept: application/vnd.api+json" \
        -H "${AUTH_HEAD}" \
        -H "Content-Type: application/vnd.api+json" \
        -d "{ \"data\": { \"attributes\": { \"app_id\": \"${LAMPIO_APP}\", \"command\": \"chown -R www-data:www-data storage\" }, \"type\": \"app_runs\" }}" \
	-sS \
	| jq -r '.data.id' \
)"

until [ "$(curl $LAMPIO_API/app_runs/$CH1_RUN_ID -H "$AUTH_HEAD" -sS | jq -r '.data.attributes.complete')" = "true" ]; do sleep 1; done
until [ "$(curl $LAMPIO_API/app_runs/$CH2_RUN_ID -H "$AUTH_HEAD" -sS | jq -r '.data.attributes.complete')" = "true" ]; do sleep 1; done
until [ "$(curl $LAMPIO_API/app_runs/$PAM_RUN_ID -H "$AUTH_HEAD" -sS | jq -r '.data.attributes.complete')" = "true" ]; do sleep 1; done
