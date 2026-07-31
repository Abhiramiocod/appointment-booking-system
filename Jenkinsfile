pipeline {
    agent any

    environment {
        APP_SERVER = "bookly-appointment.duckdns.org"
        APP_PATH = "/var/www/appointment-booking-system"
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Deploy') {
            steps {
                sshagent(credentials: ['laravel-server-key']) {
                    sh '''
ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null ubuntu@'"${APP_SERVER}"' <<EOF
set -e

cd '"${APP_PATH}"'

git pull origin main

composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan optimize:clear
php artisan optimize

sudo systemctl restart php8.5-fpm
sudo systemctl reload nginx
EOF
'''
                }
            }
        }
    }

    post {
        success {
            echo 'Deployment completed successfully.'
        }

        failure {
            echo 'Deployment failed.'
        }
    }
}