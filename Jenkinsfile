pipeline {
    agent any

    stages {
        stage('Build') {
            steps {
                composer install
                vendor/bin/phpstan analyse
            }
        }
    }
}
