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
  mkdir-this-release
  upload
  extract
  delete
  setup-storage
  symlink-storage
  chown-storage
  chown-bootstrap-cache
  dotenv
  pam
  symlink-public
@endstory

@task('composer-install')
  composer install --optimize-autoloader
@endtask

@task('zip')
  echo zipping up new deployment
  zip -r /tmp/artifact.zip . \
    -x "*\.env*" \
    -x "*\.git*" \
    --quiet
@endtask

@task('mkdir-this-release')
  lio files:new:dir {{ $app }} releases/{{ $release }}
@endtask

@task('upload')
  lio files:upload /tmp/artifact.zip {{ $app }} releases/{{ $release }}/artifact.zip
@endtask

@task('extract')
  lio files:update:unarchive {{ $app }} releases/{{ $release }}/artifact.zip
@endtask

@task('delete')
  echo deleting the zip
  rm -f /tmp/artifact.zip
  lio files:delete {{ $app }} releases/{{ $release }}/artifact.zip
@endtask

@task('setup-storage')
  if ! lio files:list {{ $app }} storage -q; then
    echo copying this releases storage dir as initial shared storage dir
    lio files:update:move {{ $app }} releases/{{ $release }}/storage /storage
  fi
@endtask

@task('symlink-storage')
  lio files:delete {{ $app }} releases/{{ $release }}/storage -y -q
  lio files:new:symlink {{ $app }} releases/{{ $release }}/storage ../../storage
@endtask

@task('chown-storage')
  echo set apache_writable on storage
  lio files:update {{ $app }} storage --apache_writable=true --recursive
@endtask

@task('chown-bootstrap-cache')
  echo set apache_writable on releases/{{ $release }}/bootstrap/cache
  lio files:update {{ $app }} releases/{{ $release }}/bootstrap/cache --apache_writable=true --recursive
@endtask

@task('dotenv')
  lio files:upload .env.live {{ $app }} releases/{{ $release }}/.env
@endtask

@task('pam')
  lio app_runs:new {{ $app }} "cd releases/{{ $release }} && php artisan migrate"
@endtask

@task('symlink-public')
  PUBLIC="$(lio files:list {{ $app }} public -j)"
  if [ "$(echo $PUBLIC | jq -r '.data.attributes.is_dir')" = 'true' ]; then
    lio files:new:dir {{ $app }} releases/{{ $release }}-pre
    lio files:update:move {{ $app }} public releases/{{ $release }}-pre/public
    lio files:new:symlink {{ $app }} public releases/{{ $release }}/public
  elif [ "$(echo $PUBLIC | jq -r '.data.attributes.is_symlink')" = 'true' ]; then
    lio files:update:symlink {{ $app }} public releases/{{ $release }}/public
  elif [ "$(echo $PUBLIC | jq -r '.errors[].status')" = '404' ]; then
    lio files:new:symlink {{ $app }} public releases/{{ $release }}/public
  fi
@endtask
