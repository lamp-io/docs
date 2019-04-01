@servers(['localhost' => '127.0.0.1'])

@setup
  if(!isset($app)){
    throw new Exception('--app must be specified');
  }
  $release = date('YmdHis');
  $lampio_api = 'https://api.lamp.io';
  $lampio_tok = trim(file_get_contents(getenv('HOME') . '/.config/lamp.io/token'));
  $auth = "\"authorization: Bearer $lampio_tok\"";
@endsetup

@story('deploy')
  composer-install
  zip
  mkdir-releases
  mkdir-this-release
  upload
  extract
  delete
  symlink-storage
  chown-storage
  chown-bootstrap-cache
  dotenv
  pam
  symlink-current
@endstory

@task('composer-install')
  composer install --optimize-autoloader --no-dev
@endtask

@task('zip')
  echo zipping up new deployment
  zip -r /tmp/artifact.zip . \
    -x "*\.env*" \
    -x "*\.git*" \
    --quiet
@endtask

@task('mkdir-releases')
  echo creating releases directory
  curl {{ $lampio_api }}/apps/{{ $app }}/files \
    -H {{ $auth }} \
    -H "Content-Type: application/vnd.api+json" \
    -H "accept: application/vnd.api+json" \
    -d "{\"data\":{\"type\":\"files\",\"id\":\"releases\",\"attributes\":{\"contents\":\"\",\"is_dir\":true}}}"  \
    -sSo /dev/null
@endtask

@task('mkdir-this-release')
  echo creating the releases/{{ $release }} dir
  curl {{ $lampio_api }}/apps/{{ $app }}/files \
    -H {{ $auth }} \
    -H "Content-Type: application/vnd.api+json" \
    -H "accept: application/vnd.api+json" \
    -d "{\"data\":{\"type\":\"files\",\"id\":\"releases/{{ $release }}\",\"attributes\":{\"contents\":\"\",\"is_dir\":true}}}"  \
    -sSo /dev/null
@endtask


@task('upload')
  echo uploading the new deployment zip
  curl {{ $lampio_api }}/apps/{{ $app }}/files \
    -F "releases/{{ $release }}/artifact.zip=@/tmp/artifact.zip" \
    -H {{ $auth }} \
    -sSo /dev/null
@endtask

@task('extract')
  echo extracting the zip remotely
  curl {{ $lampio_api }}/apps/{{ $app }}/files/releases/{{ $release }}/artifact.zip \
    -X PATCH \
    -H {{ $auth }} \
    -H "Content-Type: application/vnd.api+json" \
    -H 'accept: application/vnd.api+json' \
    -d "{\"data\":{\"type\":\"files\",\"id\":\"releases/{{ $release }}/artifact.zip\",\"attributes\":{\"command\":\"unarchive\"}}}"  \
    -sSo /dev/null
@endtask

@task('delete')
  echo deleting the zip
  rm -f /tmp/artifact.zip
  curl {{ $lampio_api }}/apps/{{ $app }}/files/releases/{{ $release }}/artifact.zip \
    -X DELETE \
    -H {{ $auth }} \
    -H 'accept: application/vnd.api+json' \
    -sSo /dev/null
@endtask

@task('symlink-storage')
  if [ "$(curl {{ $lampio_api }}/apps/{{ $app }}/files/storage -H {{ $auth }} -H "accept: application/vnd.api+json" -s | jq '.errors[].status' -r 2> /dev/null)" = "404" ]; then
    echo copying this releases storage dir as initial shared storage dir
    CP_RUN_ID="$(curl {{ $lampio_api }}/app_runs \
      -X POST \
      -H {{ $auth }} \
      -H "Content-Type: application/vnd.api+json" \
      -H "accept: application/vnd.api+json" \
      -d "{ \"data\": { \"attributes\": { \"app_id\": \"{{ $app }}\", \"command\": \"cp -a releases/{{ $release }}/storage .\" }, \"type\": \"app_runs\" }}" \
      -sS \
      | jq -r '.data.id' \
    )"
    until [ "$(curl {{ $lampio_api }}/app_runs/$CP_RUN_ID -H {{ $auth }} -sS | jq -r '.data.attributes.complete')" = "true" ]
    do
      sleep 2
    done
  fi  

  echo delete this releases storage dir
  curl {{ $lampio_api }}/apps/{{ $app }}/files/releases/{{ $release }}/storage \
    -X DELETE \
    -H {{ $auth }} \
    -H 'accept: application/vnd.api+json' \
    -sSo /dev/null

  echo symlink this releases storage dir back to the root shared storage dir
  curl {{ $lampio_api }}/apps/{{ $app }}/files \
    -X POST \
    -H {{ $auth }} \
    -H "Content-Type: application/vnd.api+json" \
    -H 'accept: application/vnd.api+json' \
    -d "{\"data\":{\"type\":\"files\",\"id\":\"releases/{{ $release }}/storage\",\"attributes\":{\"is_symlink\":true,\"target\":\"../../storage\"}}}" \
    -sSo /dev/null
@endtask

@task('chown-storage')
  echo run: chown -R www-data:www-data storage
  RUN_ID="$(curl {{ $lampio_api }}/app_runs \
    -X POST \
    -H {{ $auth }} \
    -H "Content-Type: application/vnd.api+json" \
    -H "accept: application/vnd.api+json" \
    -d "{ \"data\": { \"attributes\": { \"app_id\": \"{{ $app }}\", \"command\": \"chown -R www-data:www-data storage\" }, \"type\": \"app_runs\" }}" \
    -sS \
    | jq -r '.data.id' \
  )"
  until [ "$(curl {{ $lampio_api }}/app_runs/$RUN_ID -H {{ $auth }} -sS | jq -r '.data.attributes.complete')" = "true" ]
  do
    sleep 2
  done
@endtask

@task('chown-bootstrap-cache')
  echo run: chown -R www-data:www-data releases/{{ $release }}/bootstrap/cache
  RUN_ID="$(curl {{ $lampio_api }}/app_runs \
    -X POST \
    -H {{ $auth }} \
    -H "Content-Type: application/vnd.api+json" \
    -H "accept: application/vnd.api+json" \
    -d "{ \"data\": { \"attributes\": { \"app_id\": \"{{ $app }}\", \"command\": \"chown -R www-data:www-data releases/{{ $release }}/bootstrap/cache\" }, \"type\": \"app_runs\" }}" \
    -sS \
    | jq -r '.data.id' \
  )"
  until [ "$(curl {{ $lampio_api }}/app_runs/$RUN_ID -H {{ $auth }} -sS | jq -r '.data.attributes.complete')" = "true" ]
  do 
    sleep 2
  done
@endtask

@task('dotenv')
  echo uploading the proper .env file
  curl {{ $lampio_api }}/apps/{{ $app }}/files \
    -F "releases/{{ $release }}/.env=@.env.live" \
    -H {{ $auth }} \
    -sSo /dev/null
@endtask

@task('pam')
  echo run: php artisan migrate
  RUN_ID="$(curl {{ $lampio_api }}/app_runs \
    -X POST \
    -H {{ $auth }} \
    -H "Content-Type: application/vnd.api+json" \
    -H "accept: application/vnd.api+json" \
    -d "{ \"data\": { \"attributes\": { \"app_id\": \"{{ $app }}\", \"command\": \"cd releases/{{ $release }} && php artisan migrate\" }, \"type\": \"app_runs\" }}" \
    -sS \
    | jq -r '.data.id' \
  )"
  until [ "$(curl {{ $lampio_api }}/app_runs/$RUN_ID -H {{ $auth }} -sS | jq -r '.data.attributes.complete')" = "true" ]
  do
    sleep 2
  done
  curl {{ $lampio_api }}/app_runs/$RUN_ID -H {{ $auth }} -sS | jq -r '.data.attributes.output'
@endtask

@task('symlink-current')
  if [ "$(curl {{ $lampio_api }}/apps/{{ $app }}/files/current -H {{ $auth }} -H 'accept: application/vnd.api+json' -sS | jq -r '.data.attributes.is_symlink')" = "true" ]; then
    echo update current symlink to the new release
    curl {{ $lampio_api }}/apps/{{ $app }}/files \
      -X PATCH \
      -H {{ $auth }} \
      -H "Content-Type: application/vnd.api+json" \
      -H 'accept: application/vnd.api+json' \
      -d "{\"data\":{\"type\":\"files\",\"id\":\"current\",\"attributes\":{\"is_symlink\":true,\"target\":\"releases/{{ $release }}\"}}}" \
      -sSo /dev/null
  else
    echo symlink current to the new release
    curl {{ $lampio_api }}/apps/{{ $app }}/files \
      -X POST \
      -H {{ $auth }} \
      -H "Content-Type: application/vnd.api+json" \
      -H 'accept: application/vnd.api+json' \
      -d "{\"data\":{\"type\":\"files\",\"id\":\"current\",\"attributes\":{\"is_symlink\":true,\"target\":\"releases/{{ $release }}\"}}}" \
      -sSo /dev/null
  fi
@endtask
