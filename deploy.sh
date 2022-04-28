#!/bin/bash

STAGE=$1
NOW=$(date +%s)
UPLOAD_PATH="deploy-$NOW"
ENV_FILE_DEPLOY=".env.deploy.$STAGE"

export $(grep -v '^#' "$ENV_FILE_DEPLOY" | xargs)

echo
echo "Deploying to $STAGE..."
date
echo

if [ $STAGE = 'local' ]; then
  lftp -c "mirror --exclude vendor --exclude .env --include .env.$STAGE -R src $UPLOAD_PATH; bye" --user $SSH_USER --password $SSH_PASS sftp://$SSH_HOST
fi

if [ $STAGE = 'dev' ]; then
  lftp -c "set sftp:connect-program \"ssh -a -x -i $SSH_KEY\"; connect sftp://$SSH_USER@$SSH_HOST:$SSH_PORT; mirror --exclude vendor --exclude .env --include .env.$STAGE -R src $UPLOAD_PATH; bye"
fi

OUT=$?

if [ $OUT != 0 ]; then
  echo 'Deploy failed'
  exit 1
fi


echo 'Transfer successful, moving to target...'

if [ $STAGE = 'local' ]; then
  ssh -t $SSH_USER@$SSH_HOST << EOF
  cd "$UPLOAD_PATH"
  mv ".env.$STAGE" .env
  composer install --optimize-autoloader --no-dev
  php artisan optimize:clear
  mkdir -p $INSTALL_PATH
  mv $INSTALL_PATH "$INSTALL_PATH.$NOW"
  cd .. && mv "$UPLOAD_PATH" "$INSTALL_PATH"
  echo "$SSH_PASS"| sudo -S chown -R www-data:www-data "$INSTALL_PATH"
  exit
EOF
fi

if [ $STAGE = 'dev' ]; then
  ssh -i $SSH_KEY -t $SSH_USER@$SSH_HOST -p $SSH_PORT << EOF
  cd "$UPLOAD_PATH"
  mv ".env.$STAGE" .env
  composer install --optimize-autoloader --no-dev
  php artisan optimize:clear
  mkdir -p $INSTALL_PATH
  mv $INSTALL_PATH "$INSTALL_PATH.$NOW"
  cd .. && mv "$UPLOAD_PATH" "$INSTALL_PATH"
  exit
EOF
fi

echo 'Deploy successful'

date
echo

