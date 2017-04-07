node {
    def cont = "php"

    stage 'Test'
        checkout scm
        sh "docker container restart ${cont}"
}
