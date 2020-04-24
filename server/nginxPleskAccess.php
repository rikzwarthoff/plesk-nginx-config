<?php /** @var Template_VariableAccessor $VAR */ ?>
<?php if (!$VAR->panel->isHttpsProxyEnabled): ?>
    <?php return ?>
<?php endif ?>

server {
<?php foreach ($VAR->server->ipAddresses->all as $ipAddress): ?>
    listen <?="{$ipAddress->escapedAddress}:{$VAR->server->nginx->httpsPort}"?> ssl;
<?php endforeach ?>

<?php $sslCertificate = $VAR->panel->sslCertificate ?>
<?php if ($sslCertificate->ceFilePath): ?>
    ssl_certificate             "<?=$sslCertificate->ceFilePath?>";
    ssl_certificate_key         "<?=$sslCertificate->ceFilePath?>";
<?php endif ?>
    client_max_body_size 2048m;
    proxy_read_timeout 600;
    proxy_send_timeout 600;

<?php if (!$VAR->panel->isDefaultAccessDomain) { ?>
    server_name <?= $VAR->quote($VAR->panel->accessDomainName) ?>;
<?php } else { ?>
    server_name _;
<?php } ?>

    location / {
        proxy_pass http://127.0.0.1:8880;
        proxy_redirect http://$host:8880 $scheme://$host;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        access_log off;

        gzip on;
        gzip_types text/plain text/css application/json application/x-javascript text/xml application/xml application/xml+rss text/javascript application/javascript;
    }

    location /ws {
        proxy_pass http://127.0.0.1:8880;
        proxy_redirect off;
        proxy_http_version 1.1;
        proxy_set_header Upgrade "websocket";
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        access_log off;
    }

    location /netdata {
        proxy_pass        http://127.0.0.1:19999;
        proxy_redirect off
        proxy_set_header  X-Real-IP  $remote_addr;
        proxy_set_header        Host            $host;
        proxy_set_header  X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header        X-Forwarded-Proto $scheme;
        proxy_buffering off;
        access_log off;

    }
}
