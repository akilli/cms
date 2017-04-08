node {
    def imgName = "akilli/qnd"
    def contName = "php nginx"
    def volName = "qnd_app"

    stage 'Checkout'
        checkout scm

    stage 'Build'
        docker.build(imgName)

    stage 'Deploy'
        sh "docker-compose stop ${contName}"
        sh "docker-compose rm ${contName}"
        sh "docker volume rm ${volName}"
        sh "docker-compose up -d ${contName}"
}
