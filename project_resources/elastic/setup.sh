sudo apt-get update
sudo apt-get install openjdk-7-jre-headless -y
wget https://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-1.1.1.deb
sudo dpkg -i elasticsearch-1.1.1.deb

then install the mapper:

bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/2.0.0
