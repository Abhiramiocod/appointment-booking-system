pipeline {
    agent any

    environment {
        APP_SERVER = "13.232.226.58"
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
                sh """
                ssh -o StrictHostKeyChecking=no ubuntu@${APP_SERVER} << 'EOF'
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