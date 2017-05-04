node {
    def project = "qnd"
    def reg = "registry.test.eqmh.de"
    def img = "${reg}/${project}"
    def auth = "ci"
    def cont = "${project}-app ${project}"
    def vol = "${project}_app"

    stage 'Clean'
        deleteDir()

    stage 'Checkout'
        checkout scm
        def shortCommit = sh(returnStdout: true, script: 'git rev-parse --short HEAD').trim()
        echo "Checked out repository @ commit ${shortCommit}"

    stage 'Build'
        def oldId = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img} || true").trim()
        sh "sudo docker build -t ${img} ."
        def id = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img} || true").trim()

        if (!oldId || !oldId.equals(id)) {
            if (oldId) {
                echo "Updated image ${img}, new ID is ${id}. Old image with ID ${oldId} can be deleted after updating derived containers."
            } else {
                echo "Created new image ${img} with ID ${id}"
            }
        } else {
            echo "Image ${img} is already up-to-date."
        }

    stage 'Registry'
        withCredentials([usernamePassword(credentialsId: auth, passwordVariable: 'pass', usernameVariable: 'user')]) {
            sh "sudo docker login -u ${user} -p ${pass} ${reg}"
            sh "sudo docker push ${img}"
            echo "Successfully pushed image ${img} to ${reg}."
        }

    stage 'Live'
        sh "sudo docker-compose -p ${project} -f docker-compose.yml stop ${cont}"
        sh "sudo docker-compose -p ${project} -f docker-compose.yml rm ${cont}"
        sh "sudo docker volume rm ${vol}"
        sh "sudo docker-compose -p ${project} -f docker-compose.yml up -d --force-recreate ${cont}"
        echo "Successfully deployed ${project} erfolgreich auf Test-Server deployed"
}
