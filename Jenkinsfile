def reg = "registry.test.eqmh.de"
def app = "cms"
def img = "${reg}/${app}"
def con = "${app}-app ${app}"
def vol = "${app}_app"
def pro = "traefik"
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
        stage("Checkout") {
            steps {
                checkout scm
            }
        }

        stage("Build") {
            steps {
                script {
                    oid = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img} || true").trim()
                }

                sh "sudo docker build -t ${img} ."

                script {
                    nid = sh(returnStdout: true, script: "sudo docker inspect --format='{{.Id}}' ${img} || true").trim()
                }
            }
        }

        stage("Registry") {
            steps {
                withCredentials([usernamePassword(credentialsId: cre, passwordVariable: 'pass', usernameVariable: 'user')]) {
                    sh "sudo docker login -u ${user} -p ${pass} ${reg}"
                    sh "sudo docker push ${img}"
                }
            }
        }

        stage("Live") {
            steps {
                sh "sudo docker-compose -p ${app} -f docker-compose.yml stop ${con}"
                sh "sudo docker-compose -p ${app} -f docker-compose.yml rm -f ${con}"
                sh "sudo docker volume rm ${vol} || true"
                sh "sudo docker-compose -p ${app} -f docker-compose.yml up -d --force-recreate"
                sh "sudo docker restart ${pro}"
            }
        }

        stage("Clean") {
            steps {
                deleteDir()

                script {
                    if (oid && !oid.equals(nid)) {
                        sh "sudo docker rmi ${oid}"
                    }
                }
            }
        }
    }
}
