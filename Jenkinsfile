node {
    def img = "registry.test.eqmh.de/cms"
    def oldId = ""
    def id = ""

    stage 'Checkout'
        checkout scm

    stage 'Build'
        oldId = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img} || true").trim()
        sh "sudo docker build -t ${img} ."
        id = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img} || true").trim()

    stage 'Registry'
        withCredentials([usernamePassword(credentialsId: 'ci', passwordVariable: 'pass', usernameVariable: 'user')]) {
            sh "sudo docker login -u ${user} -p ${pass} registry.test.eqmh.de"
            sh "sudo docker push ${img}"
        }

    stage 'Live'
        sh "sudo docker-compose -p cms -f docker-compose.yml stop cms-app cms"
        sh "sudo docker-compose -p cms -f docker-compose.yml rm -f cms-app cms"
        sh "sudo docker volume rm cms_app || true"
        sh "sudo docker-compose -p cms -f docker-compose.yml up -d --force-recreate"
        sh "sudo docker restart traefik"

    stage 'Clean'
        deleteDir()

        if (oldId && !oldId.equals(id)) {
            sh "sudo docker rmi ${oldId}"
        }
}
