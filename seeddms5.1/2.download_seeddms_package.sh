# Get seeddms
VER=5.1.25
webSite=webSite
sudo rm -rf ${webSite}
mkdir -p ${webSite}
SEEDDMS_BASE=${webSite}/seeddms
SEEDDMS_HOME=${webSite}/seeddms/seeddms
#PUBLIC_CERT=${SEEDDMS_BASE}/conf/cacert.pem
#PUBLIC_CERT_SUBJ=/CN=localhost
#PRIVATE_KEY=${SEEDDMS_BASE}/conf/cakey.pem
#FORCE_SSL=0

curl -fsSL https://downloads.sourceforge.net/project/seeddms/seeddms-${VER}/seeddms-quickstart-${VER}.tar.gz | tar -xzC ${webSite}
mv ${webSite}/seeddms51x ${webSite}/seeddms
mkdir ${webSite}/seeddms/backup
mkdir -p ${webSite}/seeddms/import/admin
mv ${webSite}/seeddms/conf ${webSite}/seeddms/data/conf
mypwd=$(pwd)
cd ${webSite}/seeddms
ln -s data/conf conf
cd ${mypwd}
mkdir $SEEDDMS_HOME/ext
touch ${webSite}/seeddms/conf/ENABLE_INSTALL_TOOL
cp sources/settings.xml ${webSite}/seeddms/data/conf/settings.xml
sudo chown -R www-data:www-data ${webSite}

