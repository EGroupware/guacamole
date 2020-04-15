# Apache Guacamole in EGroupware

#### EGroupware app does the following:

* Installs all tables (or views) for Apache Guacamole
* Everything account-related is a view, not a table
* EGroupware UI to create connections (Admin >> Guacamole >> Connections)
* One has to use EGroupware to assign permissions to connections
* Guacamole UI can be used to set advanced connection options

#### Docker-Compose and other files to run Guacamole
Create /etc/egroupware-guacamole/docker-compose.yaml:
```yaml
version: 3
services:
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
      MYSQL_USER: egroupware
      MYSQL_PASSWORD: [use db_passwd from header.inc.php]
      GUACAMOLE_HOME: /etc/guacamole
    image: guacamole/guacamole
    links:
    - guacd
    ports:
    - 127.0.0.1:8888:8080/tcp
    restart: always
    volumes:
    - ./guacamole-home:/etc/guacamole
```
Create /etc/egroupware-guacamole/nginx.conf:
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
Create /etc/egroupware-guacamole/apache.conf:
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
Create /etc/egroupware-guacamole/guacamole-home/extensions:
```
mkdir -p /etc/egroupware-guacamole/guacamole-home/extensions
cd /etc/egroupware-guacamole/guacamole-home/extensions
ln -s /opt/guacamole/openid/guacamole-auth-openid-1.1.0.jar 00-guacamole-auth-openid-1.1.0.jar
```
/etc/egroupware-guacamole/guacamole-home/guacamole.properties:
```
# OpenIDConnect configuration (https://guacamole.apache.org/doc/gug/openid-auth.html#guac-openid-config)
# https://lemonldap-ng.org/documentation/latest/applications/guacamole
openid-authorization-endpoint: https://example.org/egroupware/openid/endpoint.php/authorize
openid-jwks-endpoint: https://example.org/egroupware/openid/endpoint.php/jwks
openid-issuer: https://example.org
openid-client-id: guacamole
openid-redirect-uri: https://example.org/guacamole/
openid-username-claim-type: sub
openid-scope: openid profile email
```
Create an OpenID connect client in EGroupware (Admin >> OpenID / OAUth2 Server >> Clients)
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
```
**You have to replace https://example.org/ in all above files with the URL you use!**
#### list of ressources / further reading:

* 