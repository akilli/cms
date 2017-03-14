node {
    def project = "app"
    def vol = "/docker/${project}"
    def cont = "eqmh_nginx_1 eqmh_php_1"

    stage 'Test'
        dir("${vol}") {
            sh "realpath $(basename .)"
            checkout scm
            def shortCommit = sh(returnStdout: true, script: 'git rev-parse --short HEAD').trim()
            echo "Repository @ Commit ${shortCommit} ausgecheckt"
        }

        sh "ls -al ${vol}"
        sh "ls -al ${WORKSPACE}"
        sh "docker container restart ${cont}"
}
