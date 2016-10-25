# Contribueren aan de stek

## Installatie

Installeer een stack met Apache2 en MySQL.

Maak in je `hosts` bestand een verwijzing van `dev.csrdelft.nl` naar `localhost`.

De volgende configuratie werkt goed voor Apache2, let op de `php_value include_path ...`.

```
<VirtualHost dev.csrdelft.nl:80>
    DocumentRoot "<repo root>\htdocs"
    ServerName dev.csrdelft.nl
    ServerAlias dev.csrdelft.nl
    ErrorLog "logs/dev.csrdelft.nl-error.log"
    #tell php to look in the lib-dir
    php_value include_path "<repo root>\lib"
    <Directory "<repo root>\htdocs">
        AllowOverride All
        Order Allow,Deny
        Allow from all
        Require all granted
    </Directory>
</VirtualHost>
```

Enable `mod_ldap` en .. in `php.ini`