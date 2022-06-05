webSite=webSite
SEEDDMS_BASE=${webSite}/seeddms
sudo mv ${SEEDDMS_BASE}/data/conf/cacert.pem  ${SEEDDMS_BASE}/data/conf/cacert.pem.old
sudo mv ${SEEDDMS_BASE}/data/conf/cakey.pem  ${SEEDDMS_BASE}/data/conf/cakey.pem.old
sudo cp  /etc/letsencrypt/live/dms.biobank.org.tw/fullchain.pem ${SEEDDMS_BASE}/data/conf/cacert.pem
sudo cp  /etc/letsencrypt/live/dms.biobank.org.tw/privkey.pem ${SEEDDMS_BASE}/data/conf/cakey.pem

