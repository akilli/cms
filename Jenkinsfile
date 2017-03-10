node {
    def project = "app"
    def vol = "/docker/${project}"
    def cont = "eqmh_nginx_1 eqmh_php_1"

    stage 'Clean'
        deleteDir()

    stage 'Checkout'
        checkout scm
        def shortCommit = sh(returnStdout: true, script: 'git rev-parse --short HEAD').trim()
        echo "Repository @ Commit ${shortCommit} ausgecheckt"

    stage 'Deploy'
        sh "rm -rf ${vol}/* ${vol}/.[^.]*"
        sh "mv ${WORKSPACE}/* ${vol}/"
        sh "ls -al ${vol}"
        sh "docker container restart ${cont}"
}
