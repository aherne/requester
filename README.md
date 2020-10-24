# requester
Light weight cURL wrapper that completely hides chaotic native library underneath through a full featured OOP layer
https://stackoverflow.com/questions/4221874/how-do-i-allow-https-for-apache-on-localhost
> openssl req -x509 -newkey rsa:2048 -keyout mykey.key -out mycert.pem -days 365 -nodes
=> mycert.pem  mykey.key   

> sudo -i
> cp mycert.pem /etc/ssl/certs
> cp mykey.key /etc/ssl/private
> cd /etc/apache2/sites-available
> nano default-ssl.conf
	SSLCertificateFile      /etc/ssl/certs/mycert.pem
	SSLCertificateKeyFile /etc/ssl/private/mykey.key
> a2ensite default-ssl.conf
> service apache2 reload