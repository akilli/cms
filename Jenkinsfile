def reg = "registry.ayhan.biz"
def app = "cms"
def img = "${reg}/${app}"
def srv = "${app}-app ${app}"
def vol = "${app}_app"
def cre = "ci"
def oid = ""
def nid = ""

pipeline {
    agent any

    options {
        buildDiscarder(logRotator(numToKeepStr: '1'))
        disableConcurrentBuilds()
    }

    stages {
        stage("Build") {
            steps {
                script {
                    oid = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img} || true").trim()
                }

                sh "sudo docker build -t ${img} ."

                script {
                    nid = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img}").trim()
                }
            }
        }

        stage("Registry") {
            steps {
                withCredentials([usernamePassword(credentialsId: cre, passwordVariable: 'pass', usernameVariable: 'user')]) {
                    sh "sudo docker login -u ${user} -p ${pass} ${reg}"
                }

                sh "sudo docker push ${img}"
            }
        }

        stage("Deploy") {
            steps {
                sh "sudo docker-compose -p ${app} -f docker-compose.yml rm -sf ${srv}"
                sh "sudo docker volume rm ${vol} || true"
                sh "sudo docker-compose -p ${app} -f docker-compose.yml up -d --force-recreate"
            }
        }

        stage("Clean") {
            steps {
                script {
                    if (oid && !oid.equals(nid)) {
                        sh "sudo docker rmi ${oid}"
                    }
                }
            }
        }
    }

    post {
        always {
            deleteDir()
        }
    }
}
