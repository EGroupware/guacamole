# Apache Guacamole managed by EGroupware

![GuacamoleWindows2016Server](https://user-images.githubusercontent.com/972180/79756536-0df90280-831b-11ea-97ff-b3043d7b31e4.png)

#### EGroupware Guacamole app does the following:

* Installs all tables (or views) for Apache Guacamole
* Everything account-related is a view, not a table
* EGroupware UI to create connections (Admin >> Guacamole >> Connections)
* One has to use EGroupware to assign permissions to connections
* Guacamole UI can be used to set advanced connection options

> The app requires accounts stored in SQL. A workaround for using LDAP or ActiveDirectory for account storage (not just authentication), is to regularly use setup to migrate users and groups to SQL.

#### List of resources / further reading:
* [Guacamole installation instructions for EGroupware via package-manager](https://github.com/EGroupware/egroupware/wiki/Apache-Guacamole-managed-by-EGroupware)
* [Apache Guacamole website](https://guacamole.apache.org)
* [Using Guacamole section of Guacamole manual](https://guacamole.apache.org/doc/gug/using-guacamole.html)
* [Administration section of Guacamole manual](https://guacamole.apache.org/doc/gug/administration.html) (keep in mind to use EGroupware UI to assign connection permissions!)
* [Frequently Asked Questions from Guacamole](https://guacamole.apache.org/faq/)
* [Guacamole category in our forum](https://help.egroupware.org/c/apps/guacamole) with some informative articles

#### Instructions to integrate Guacamole in an EGroupware installation via Docker
> A deb or rpm package installation via egroupware-guacamole package, available from our usual repository, does NOT require anything mentioned here!

Following files are fragments to be included in an [EGroupware Docker](https://github.com/EGroupware/egroupware/tree/master/doc/docker) or [development](https://github.com/EGroupware/egroupware/tree/master/doc/docker/development) installation. You need to replace example.org with your domain!

First you need to create a database account for Guacamole:
> docker-compose has problems with passwords containing special chars, use a eg. the following to create a safe password:
```
openssl rand --hex 16 # use the output for [guacamole-user-password] below
docker-compose exec db mysql --execute "GRANT ALL ON egroupware.* TO guacamole@`%` IDENTIFIED BY 'guacamole-user-password'"
```

docker-compose.yaml:
```yaml
  guacd:
    container_name: guacamole-guacd
    image: guacamole/guacd
    restart: always
    volumes:
    - ./drive:/drive:rw
    - ./record:/record:rw

  guacamole:
    container_name: guacamole
    depends_on:
    - guacd
    - db
    environment:
      GUACD_HOSTNAME: guacd
      MYSQL_HOSTNAME: db
      MYSQL_DATABASE: egroupware
      MYSQL_USER: guacamole
      MYSQL_PASSWORD: guacamole-user-password
      GUACAMOLE_HOME: /etc/guacamole
      # see https://github.com/apache/guacamole-client/blob/master/guacamole-docker/bin/start.sh#L552
      OPENID_AUTHORIZATION_ENDPOINT: https://example.org/egroupware/openid/endpoint.php/authorize
      OPENID_JWKS_ENDPOINT: https://example.org/egroupware/openid/endpoint.php/jwks
      OPENID_ISSUER: https://example.org
      OPENID_CLIENT_ID: guacamole
      OPENID_REDIRECT_URI: https://example.org/guacamole/
    image: guacamole/guacamole
    links:
    - guacd
    ports:
    - 127.0.0.1:8888:8080/tcp
    restart: always
    volumes:
    - /etc/guacamole:/etc/guacamole
```
nginx.conf:
```yaml
    # Guacamole to include in your server-block
    location /guacamole/ {
        proxy_pass http://127.0.0.1:8888/guacamole/;
        proxy_buffering off;
        proxy_http_version 1.1;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $http_connection;
        proxy_cookie_path /guacamole/ /;
        access_log off;
        # allow large uploads (default=1m)
        # 4096m = 4GByte
        client_max_body_size 4096m;
    }
```
apache.conf:
```
    # Apache config to include in your vhost
    <Location /guacamole/>
        Order allow,deny
        Allow from all
        ProxyPass http://127.0.0.1:8888/guacamole/ flushpackets=on
        ProxyPassReverse http://127.0.0.1:8888/guacamole/
    </Location>
    
    <Location /guacamole/websocket-tunnel>
        Order allow,deny
        Allow from all
        ProxyPass ws://127.0.0.1:8888/guacamole/websocket-tunnel
        ProxyPassReverse ws://127.0.0.1:8888/guacamole/websocket-tunnel
    </Location>
```
Create /etc/guacamole to be mounted into the container:
```
mkdir -p /etc/egroupware-guacamole/guacamole-home
```
/etc/guacamole/guacamole.properties:
```
# OpenIDConnect configuration (https://guacamole.apache.org/doc/gug/openid-auth.html#guac-openid-config)
openid-username-claim-type: sub
openid-scope: openid profile email
```
The app installation creates an OpenID connect client in EGroupware (Admin >> OpenID / OAUth2 Server >> Clients), but you need to check for the correct Redirect URI and Index URL!
```
Name: Guacamole
Identifier: guacamole
Secret:
Redirect URI: https://example.org/guacamole/
Allowed Grants: Implicit
Limit Scopes:
Status: Active
Access-Token TTL: Use default of: 1 Hour
Refresh-Token TTL: Use default of: 1 Month
[Manage as EGroupware application]
Application name: guacamole
Start URL: https://example.org/guacamole/
Allowed for: [Group Default]
Icon:
```
> You have to replace https://example.org/ in all above files with the URL you use!
