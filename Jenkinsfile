node {
    def vol = "/docker/app/qnd"
    def cont = "eqmh_php_1"

    stage 'Test'
        dir("${vol}") {
            checkout scm
            def shortCommit = sh(returnStdout: true, script: 'git rev-parse --short HEAD').trim()
            echo "Repository @ Commit ${shortCommit} ausgecheckt"
        }

        sh "ls -al ${vol}"
        sh "ls -al ${WORKSPACE}"
        sh "docker container restart ${cont}"
}
