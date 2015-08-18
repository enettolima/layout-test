sudo apt-get update
sudo apt-get install openjdk-7-jre-headless -y
wget https://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-1.3.1.deb
sudo dpkg -i elasticsearch-1.1.1.deb
remember to hold the package back: apt-mark hold elasticsearch

then install the mapper:

bin/plugin -install fr.pilato.elasticsearch.river/fsriver/1.3.1
bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/2.0.0
