# usersapi
Users API (Symfony): http://usersapi.dev

First : Install virtual host on apache 

 1- create anew file usersapi.dev in your vhosts folder.
 2- add this code to it.
  <VirtualHost *:80>
      ServerName usersapi.dev
      ServerAlias www.usersapi.dev

      DocumentRoot /var/www/usersapi/public
      <Directory /var/www/usersapi/public>
          AllowOverride All
          Order Allow,Deny
          Allow from All
      </Directory>

      # uncomment the following lines if you install assets as symlinks
      # or run into problems when compiling LESS/Sass/CoffeeScript assets
      # <Directory /var/www/usersapi>
      #     Options FollowSymlinks
      # </Directory>

      ErrorLog /var/log/apache2/usersapi_error.log
      CustomLog /var/log/apache2/usersapi_access.log combined
  </VirtualHost>

3- add vhost name in hosts file in /etc/hosts
127.0.0.1   usersapi.dev

4- restart apache using #service apache2 restart
 
Second: Install Project ( You should have symfony installed)
1- get clone from project in /var/www
2- install required bundles 


Third: Just Try >> http://usersapi.dev/api/v1/users







