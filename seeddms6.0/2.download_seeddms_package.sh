# Get seeddms
VER=6.0.18
webSite=webSite
sudo rm -rf ${webSite}
mkdir -p ${webSite}
SEEDDMS_BASE=${webSite}/seeddms
SEEDDMS_HOME=${webSite}/seeddms/seeddms
curl -fsSL https://downloads.sourceforge.net/project/seeddms/seeddms-${VER}/seeddms-quickstart-${VER}.tar.gz | tar -xzC ${webSite}
mv ${webSite}/seeddms60x ${webSite}/seeddms
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

