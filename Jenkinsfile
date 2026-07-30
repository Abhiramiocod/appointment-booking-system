stage('Deploy') {
    steps {
        sh """
ssh -o StrictHostKeyChecking=no ubuntu@${APP_SERVER} <<'EOF'
set -e

cd ${APP_PATH}

git pull origin main

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan optimize:clear
php artisan optimize

sudo systemctl restart php8.5-fpm
sudo systemctl reload nginx
EOF
"""
    }
}