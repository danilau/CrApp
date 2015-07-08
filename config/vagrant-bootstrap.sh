su -
# Install compilers
yum -y install gcc gcc-c++
# Install Git
yum -y install git
# Install Remi repository
rpm -Uvh http://rpms.famillecollet.com/remi-release-20.rpm
# Install Apache (httpd) Web server and PHP 5.6
yum -y --enablerepo=remi,remi-php56 install httpd php php-common
# Install PHP 5.6 modules
yum -y --enablerepo=remi,remi-php56 install php-devel php-pecl-apcu php-cli php-pear php-pdo php-mysqlnd php-pgsql php-pecl-mongo php-sqlite php-pecl-memcache php-pecl-memcached php-gd php-mbstring php-mcrypt php-xml
# Install Redis
yum -y install redis
# Install hiredis and phpiredis
git clone git://github.com/redis/hiredis.git
cd hiredis
make && make install
cd ~

git clone https://github.com/nrk/phpiredis.git
cd phpiredis
phpize && ./configure --enable-phpiredis
make && make install
cd ~

# Add info about timezone
cat >> /etc/php.ini <<EOF
extension=phpiredis.so
date.timezone='Europe/Minsk'
EOF
# Create vh
cat >> /etc/httpd/conf/httpd.conf <<EOF
<Directory /var/apps/crapp/web>
  AllowOverride All
  Order allow,deny
  Allow from all
  Require all granted
</Directory>
<VirtualHost *:80>
  ServerName crapp.dev
  ServerAlias www.crapp.dev
  DocumentRoot /var/apps/crapp/web
  ErrorLog /var/apps/crapp/error.log
  CustomLog /var/apps/crapp/access.log common
</VirtualHost>
EOF
# Comment lines in welcome.conf
sed -i 's/^/#/' /etc/httpd/conf.d/welcome.conf

# Start Apache HTTP server (httpd) and autostart Apache HTTP server (httpd) on boot
systemctl start httpd.service
systemctl enable httpd.service
systemctl start redis.service
systemctl enable redis.service

sleep 5

yum -y install dos2unix

cd /var/apps/crapp

dos2unix build-dev

dos2unix yii