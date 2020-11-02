[www.mozilla.jp](https://www.mozilla.jp/) は静的サイトに移行しました。新しいレポジトリは [www.mozilla.jp](https://github.com/mozilla-japan/www.mozilla.jp) です。これは旧一般社団法人 Mozilla Japan から引き継いだ古い PHP サイトのアーカイブです。

* * *

# ローカル環境での動かし方

## Apache 2.4 on Ubuntu 18.04LTS

1. 必要なパッケージをインストールする。
   
   ```bash
   $ sudo apt install apache2 libapache2-mod-php
   ```
   
2. `/etc/apache2/sites-available/mozilla.jp.conf` を以下の内容で作成する。
   
   ```
   <VirtualHost *:50080>
     ServerName localhost
     ServerAdmin webmaster@localhost
     DocumentRoot /path/to/this/repository
     <Directory "/path/to/this/repository">
       Require all granted
     </Directory>
     LogLevel info
     ErrorLog ${APACHE_LOG_DIR}/error.log
     CustomLog ${APACHE_LOG_DIR}/access.log combined
     <IfModule mime_module>
       AddType application/x-httpd-php .html
     </IfModule>
   </VirtualHost>
   
   Listen 50080
   ```
   
3. モジュールを有効化しApacheを再起動する。
   
   ```
   $ sudo a2enmod php7.2
   $ sudo a2enmod include
   $ sudo a2ensite mozilla.jp
   $ sudo service apache2 restart
   ```
   
4. ブラウザで `http://localhost:50080/` を開く。
