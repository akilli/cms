node {
    def project = "qnd"
    def reg = "registry.test.eqmh.de"
    def img = "${reg}/${project}"
    def auth = "ci"
    def cont = "${project}-app ${project}"
    def vol = "${project}_app"
    def proxy = "traefik"
    def oldId = ""
    def id = ""

    stage 'Checkout'
        checkout scm

    stage 'Build'
        oldId = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img} || true").trim()
        sh "sudo docker build -t ${img} ."
        id = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img} || true").trim()

    stage 'Registry'
        withCredentials([usernamePassword(credentialsId: auth, passwordVariable: 'pass', usernameVariable: 'user')]) {
            sh "sudo docker login -u ${user} -p ${pass} ${reg}"
            sh "sudo docker push ${img}"
            echo "Successfully pushed image ${img} to ${reg}."
        }

    stage 'Live'
        sh "sudo docker-compose -p ${project} -f docker-compose.yml stop ${cont}"
        sh "sudo docker-compose -p ${project} -f docker-compose.yml rm -f ${cont}"
        sh "sudo docker volume rm ${vol} || true"
        sh "sudo docker-compose -p ${project} -f docker-compose.yml up -d --force-recreate"
        sh "sudo docker stop ${proxy}"
        sh "sudo docker start ${proxy}"
        echo "Successfully deployed ${project} on live server"

    stage 'Clean'
        deleteDir()

        if (!oldId || !oldId.equals(id)) {
            if (oldId) {
                sh "sudo docker rmi ${oldId}"
                echo "Updated image ${img} to new ID ${id} and removed old image with ID ${oldId}."
            } else {
                echo "Created new image ${img} with ID ${id}"
            }
        } else {
            echo "Image ${img} is already up-to-date."
        }
}
