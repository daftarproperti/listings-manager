pipeline {
    agent any

    environment {
        // Each Jenkins build should be a unique project name for docker compose
        // to make sure each build runs the test in an isolated environment.
        projectName = "mls_${BUILD_NUMBER}"
    }

    stages {
        stage('Build') {
            steps {
                sh 'composer install'
                sh 'vendor/bin/phpstan analyse'
            }
        }
        stage('Test') {
            steps {
                script {
                    sh """
                    docker network create global_network || true
                    docker-compose -p ${projectName} -f test-env/docker-compose.yml build
                    docker-compose -p ${projectName} -f test-env/docker-compose.yml run --rm test-app
                    """
                }
            }
            post {
                always {
                    // Clean up docker resources used above
                    sh "docker-compose -p ${projectName} -f test-env/docker-compose.yml down -v --rmi local || true"
                }
            }
        }
    }
}
