#!/bin/bash

# ************************************************
# CONFIGURATION
# ************************************************

CFG_CREATE_VIBUS_DIR_STRUCT=true

CFG_YUM_INSTALL_EPEL_RELEASE=true
CFG_YUM_INSTALL_REMI_RELEASE=true
CFG_YUM_UPGRADE=true

CFG_YUM_INSTALL_FTP=true
CFG_YUM_INSTALL_WGET=true
CFG_YUM_INSTALL_CURL=true
CFG_YUM_INSTALL_HTOP=true
CFG_YUM_INSTALL_SCREEN=true
CFG_YUM_INSTALL_NET_TOOLS=true
CFG_YUM_INSTALL_NMAP=true
CFG_YUM_INSTALL_TELNET=true
CFG_YUM_INSTALL_NANO=true
CFG_YUM_INSTALL_GIT=true
CFG_YUM_INSTALL_COMPOSER=true
CFG_YUM_INSTALL_LINKCHECKER=true

CFG_YUM_INSTALL_MC=true
CFG_CONFIGURE_MC=true

CFG_YUM_INSTALL_OPENSSH_SERVER=true
CFG_VIBUS_CREATE_DIR_OPENSSH_SERVER=true
CFG_CREATE_AUTHORIZED_KEYS_FILE=true

CFG_YUM_INSTALL_LOGROTATE=true
CFG_VIBUS_CREATE_DIR_LOGROTATE=true

CFG_YUM_INSTALL_FIREWALLD=true
CFG_VIBUS_CREATE_DIR_FIREWALLD=true
CFG_FIREWALLD_CREATE_SERVER_ZONE=true
CFG_FIREWALLD_SKIP_INTERFACES_MODIFY=false
CFG_FIREWALLD_SET_SERVER_ZONE_AS_DEFAULT=true

CFG_YUM_INSTALL_FAIL2BAN=true
CFG_VIBUS_CREATE_DIR_FAIL2BAN=true

CFG_YUM_INSTALL_HTTPD=true
CFG_YUM_INSTALL_HTTPD_MOD_SSL=true
CFG_VIBUS_CREATE_DIR_HTTPD=true

CFG_YUM_INSTALL_CERTBOT=true
CFG_VIBUS_CREATE_DIR_CERT=true

CFG_YUM_INSTALL_MARIADB_SERVER=true
CFG_VIBUS_CREATE_DIR_MARIADB_SERVER=true

CFG_YUM_INSTALL_VSFTPD=true
CFG_VIBUS_CREATE_DIR_VSFTPD=true

CFG_YUM_INSTALL_POSTFIX=true
CFG_VIBUS_CREATE_DIR_POSTFIX=true

CFG_YUM_INSTALL_PHP=true
CFG_YUM_INSTALL_PHP_VER_72=true
CFG_YUM_INSTALL_PHP_MODULES=true
CFG_VIBUS_CREATE_DIR_PHP_FPM=true

CFG_ADD_VIBUS_USER=true
CFG_ADD_APACHE_TO_VIBUS_GROUP=true

CFG_VIBUS_CREATE_BACKUPS_DIR=true
CFG_VIBUS_CREATE_SCRIPTS_DIR=true
CFG_VIBUS_CREATE_SITES_DIR=true

CFG_CREATE_HOSTNAME_DOMAIN_DIR=true

# ************************************************
# FUNCTIONS
# ************************************************

yum_install () {

    if ! [ "$2" == "" ]; then
        printf "%s" "$2"
    else
        printf "Install "
    fi;

    printf "\"$1\" .. "

    if [ "`rpm -qa $1`" == "" ]; then
        STDERR="$(yum install -y $1 2>&1 > /dev/null)"
        if [ $? -eq 0 ]; then echo "OK"; else echo "ERR #$?: $STDERR"; exit; fi
    else
        echo "ALREADY INSTALLED"
    fi
}

yum_ntd_install () {
    if ! [ "$2" == "" ]; then
        printf "%s" "$2"
    else
        printf "Install "
    fi;

    printf "\"$1\" .. "

    if [ "`rpm -qa $1`" == "" ]; then
        STDERR="$(yum install -y $1 2>&1 > /dev/null)"
        if [ $? -eq 0 ]; then
            echo "OK";
        else
            if [ $? -eq 1 ]; then echo "ALREADY INSTALLED VIA ANOTHER PACKAGE"; else echo "ERR #$?: $STDERR" >&2; exit; fi
        fi
    else
        echo "ALREADY INSTALLED"
    fi
}

yum_if_install () {
    if $1; then
        yum_install $2
    fi
}

# ************************************************
# USER INPUT
# ************************************************

if $CFG_CREATE_HOSTNAME_DOMAIN_DIR; then
    read -p "Enter main domain [default `hostname`]: " DOMAIN

    if [[ -z "$DOMAIN" ]]; then
        DOMAIN=`hostname`
    fi
fi

# ************************************************
# MAIN CODE
# ************************************************

if $CFG_ADD_VIBUS_USER; then
    printf 'Add user "vibus" .. '
    if [ "`cat /etc/passwd | grep "^vibus"`" == "" ]; then
        useradd vibus >/dev/null 2>&1
        if [ $? -eq 0 ]; then echo "OK "; else echo "ERR #$?: " >&2; exit; fi
    else
        echo "ALREADY EXISTS"
    fi
fi

# ------------------------------------------------

if $CFG_CREATE_VIBUS_DIR_STRUCT; then

    echo 'Create vibus directories ..'

    mkdir -p /opt/vibus
    chown root:vibus /opt/vibus
    chmod 0750 /opt/vibus
    echo '- vibus dir .. OK';

    if $CFG_VIBUS_CREATE_SITES_DIR; then
        mkdir -p /opt/vibus/sites
        chown root:vibus /opt/vibus/sites
        echo '- sites dir .. OK';
    fi

    if $CFG_VIBUS_CREATE_SCRIPTS_DIR; then
        mkdir -p /opt/vibus/scripts
        echo '- scripts dir .. OK';
    fi

    if $CFG_VIBUS_CREATE_BACKUPS_DIR; then
        mkdir -p /opt/vibus/backups
        mkdir -p /opt/vibus/backups/scripts
        mkdir -p /opt/vibus/backups/store
        echo '- backups dir .. OK';
    fi

    mkdir -p /opt/vibus/services

    if $CFG_VIBUS_CREATE_DIR_FAIL2BAN; then
        mkdir -p /opt/vibus/services/fail2ban
        mkdir -p /opt/vibus/services/fail2ban/conf
        mkdir -p /opt/vibus/services/fail2ban/logs
        echo '- fail2ban dir .. OK';
    fi

    if $CFG_VIBUS_CREATE_DIR_HTTPD; then
        mkdir -p /opt/vibus/services/httpd
        mkdir -p /opt/vibus/services/httpd/conf
        mkdir -p /opt/vibus/services/httpd/certs
        mkdir -p /opt/vibus/services/httpd/logs
        mkdir -p /opt/vibus/services/httpd/vhosts
        echo '- httpd dir .. OK';
    fi

    if $CFG_VIBUS_CREATE_DIR_PHP_FPM; then
        mkdir -p /opt/vibus/services/php-fpm
        mkdir -p /opt/vibus/services/php-fpm/conf
        mkdir -p /opt/vibus/services/php-fpm/socks
        mkdir -p /opt/vibus/services/php-fpm/logs
        mkdir -p /opt/vibus/services/php-fpm/vhosts
        echo '- php-fpm dir .. OK';
    fi

    if $CFG_VIBUS_CREATE_DIR_LOGROTATE; then
        mkdir -p /opt/vibus/services/logrotate
        mkdir -p /opt/vibus/services/logrotate/conf
        mkdir -p /opt/vibus/services/logrotate/services
        mkdir -p /opt/vibus/services/logrotate/vhosts
        mkdir -p /opt/vibus/services/logrotate/vhosts/httpd
        mkdir -p /opt/vibus/services/logrotate/vhosts/php-fpm
        echo '- logrotate dir .. OK';
    fi

    if $CFG_VIBUS_CREATE_DIR_POSTFIX; then
        mkdir -p /opt/vibus/services/postfix
        mkdir -p /opt/vibus/services/postfix/conf
        echo '- postfix dir .. OK';
    fi

    if $CFG_VIBUS_CREATE_DIR_OPENSSH_SERVER; then
        mkdir -p /opt/vibus/services/ssh
        mkdir -p /opt/vibus/services/ssh/conf
        echo '- ssh dir .. OK';
    fi

    if $CFG_VIBUS_CREATE_DIR_MARIADB_SERVER; then
        mkdir -p /opt/vibus/services/mariadb
        mkdir -p /opt/vibus/services/mariadb/conf
        echo '- mariadb dir .. OK';
    fi
    
    if $CFG_VIBUS_CREATE_DIR_VSFTPD; then
        mkdir -p /opt/vibus/services/vsftpd
        mkdir -p /opt/vibus/services/vsftpd/conf
        mkdir -p /opt/vibus/services/vsftpd/conf/users.conf
        mkdir -p /opt/vibus/services/vsftpd/logs
        echo '- vsftpd dir .. OK';    
    fi
fi

# ------------------------------------------------

if $CFG_YUM_INSTALL_REMI_RELEASE; then

    printf 'Install "epel-release" repository ..'

    if [ "`rpm -qa epel-release`" == "" ]; then
    yum -y --nogpgcheck install epel-release >/dev/null 2>&1
        if [ $? -eq 0 ]; then echo "OK "; else echo "ERR #$?: " >&2; exit; fi
    else
        echo "ALREADY INSTALLED"
    fi
fi


# ------------------------------------------------

if $CFG_YUM_INSTALL_REMI_RELEASE; then

    printf 'Install "remi-release" repository ..'

    if [ "`rpm -qa remi-release`" == "" ]; then
    yum -y --nogpgcheck install http://rpms.remirepo.net/enterprise/remi-release-7.rpm >/dev/null 2>&1
        if [ $? -eq 0 ]; then echo "OK "; else echo "ERR #$?: " >&2; exit; fi
    else
        echo "ALREADY INSTALLED"
    fi
fi

# ------------------------------------------------

if $CFG_YUM_UPGRADE; then

    printf 'Upgrade installed packages .. '

    yum -y upgrade >/dev/null 2>&1

    if [ $? -eq 0 ]; then echo "OK "; else echo "ERR #$?: " >&2; exit; fi

fi

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_FTP ftp
yum_if_install $CFG_YUM_INSTALL_WGET wget
yum_if_install $CFG_YUM_INSTALL_CURL curl
yum_if_install $CFG_YUM_INSTALL_HTOP htop
yum_if_install $CFG_YUM_INSTALL_SCREEN screen
yum_if_install $CFG_YUM_INSTALL_NET_TOOLS net-tools
yum_if_install $CFG_YUM_INSTALL_NMAP nmap
yum_if_install $CFG_YUM_INSTALL_TELNET telnet
yum_if_install $CFG_YUM_INSTALL_NANO nano
yum_if_install $CFG_YUM_INSTALL_GIT git
yum_if_install $CFG_YUM_INSTALL_COMPOSER composer

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_MC mc

if $CFG_CONFIGURE_MC; then
    printf 'Configure "mc" ..'

    if ! `cat ~/.bash_profile | grep "export VISUAL"`; then
        printf "\nexport VISUAL=\"mcedit\"" >> ~/.bash_profile
    fi

    if ! `cat ~/.bash_profile | grep "export EDITOR"`; then
        printf "\nexport EDITOR=\"mcedit\""  >> ~/.bash_profile
    fi

    echo "OK"
fi

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_OPENSSH_SERVER openssh-server

if $CFG_CREATE_AUTHORIZED_KEYS_FILE; then
    echo 'Configure ssh ..'

    printf "%s" "- create authorized_keys file .. "
    if [ ! -d ~/.ssh ]; then
        mkdir -p ~/.ssh
    fi

    if [ ! -f ~/.ssh/authorized_keys ]; then
        touch ~/.ssh/authorized_keys
        echo 'OK'
    else
        echo 'ALREADY EXISTS'
    fi

fi

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_FIREWALLD firewalld

if $CFG_FIREWALLD_CREATE_SERVER_ZONE; then
    echo "Configure firewalld .. "

    if ! $CFG_FIREWALLD_SKIP_INTERFACES_MODIFY; then

        printf "%s" "- check IFACEs and IFACEs conf files .. "

        IFACES=`ip -o link show | awk '{print $2,$9}' | grep 'UP' | awk '{print substr($1, 1, length($1)-1)}'`

        if [ -z "$IFACES" ]; then
            echo "ERR: IFACES that have status UP not found"
            exit
        fi

        for IFACE in $IFACES; do
            if [ ! -f /etc/sysconfig/network-scripts/ifcfg-$IFACE ]; then
                echo "ERR: not found IFACE $IFACE configuration file: /etc/sysconfig/network-scripts/ifcfg-$IFACE"
                exit
            fi
        done

        echo "OK"
    fi

    printf "%s" "- create WEBSERVER zone .. "

    if ! [ "`firewall-cmd --state`" == "running" ]; then
        systemctl start firewalld >/dev/null 2>&1
    fi

    if ! [ "`firewall-cmd --get-zones | grep webserver`" == "" ]; then
        firewall-cmd --permanent --delete-zone=webserver >/dev/null 2>&1
    fi

    firewall-cmd --permanent --new-zone=webserver >/dev/null 2>&1
    firewall-cmd --reload >/dev/null 2>&1

    if ! $CFG_FIREWALLD_SKIP_INTERFACES_MODIFY; then
        for IFACE in $IFACES; do
            firewall-cmd --zone=webserver --permanent --add-interface=$IFACE >/dev/null 2>&1
            if [ "`cat /etc/sysconfig/network-scripts/ifcfg-$IFACE | grep ZONE=webserver`" == "" ]; then
                cp /etc/sysconfig/network-scripts/ifcfg-$IFACE /etc/sysconfig/network-scripts/ifcfg-$IFACE.vibus_bak
                printf "\nZONE=webserver" >> /etc/sysconfig/network-scripts/ifcfg-$IFACE
            fi
        done
    fi

    firewall-cmd --zone=webserver --permanent --add-service=ftp >/dev/null 2>&1
    firewall-cmd --zone=webserver --permanent --add-service=ssh >/dev/null 2>&1
    firewall-cmd --zone=webserver --permanent --add-service=http >/dev/null 2>&1
    firewall-cmd --zone=webserver --permanent --add-service=https >/dev/null 2>&1

    firewall-cmd --zone=webserver --permanent --add-icmp-block-inversion >/dev/null 2>&1
    firewall-cmd --zone=webserver --permanent --add-icmp-block=echo-request >/dev/null 2>&1

    firewall-cmd --reload >/dev/null 2>&1
    echo "OK"

    if $CFG_FIREWALLD_SET_SERVER_ZONE_AS_DEFAULT; then
        printf "%s" "- set WEBSERVER zone as DEFAULT .. "
        firewall-cmd --set-default-zone=webserver >/dev/null 2>&1
        firewall-cmd --reload >/dev/null 2>&1
        echo "OK"
    fi


fi

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_FAIL2BAN fail2ban

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_LOGROTATE logrotate

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_HTTPD httpd
yum_if_install $CFG_YUM_INSTALL_HTTPD_MOD_SSL mod_ssl

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_CERTBOT certbot

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_MARIADB_SERVER mariadb-server

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_VSFTPD vsftpd

# ------------------------------------------------

yum_if_install $CFG_YUM_INSTALL_POSTFIX postfix

# ------------------------------------------------

if $CFG_YUM_INSTALL_PHP; then

    if $CFG_YUM_INSTALL_PHP_VER_72; then

        echo 'Install "php 7.2" ..'

        yum_install "yum-utils" "- install "

        echo '- set default php to 7.2 ..'
        yum-config-manager --enable remi-php72 >/dev/null 2>&1
        if [ $? -eq 0 ]; then echo "  - OK "; else echo "  - ERR #$?: " >&2; exit; fi

        yum_install php "- install "

    else

        yum_install php

    fi


    if $CFG_YUM_INSTALL_PHP_MODULES; then
        echo '- install php modules ..'
        declare -a PHP_MODULES=("php-fpm" "php-mcrypt" "php-mbstring" "php-intl" "php-gd" "php-curl" "php-mysql" "php-pdo" "php-zip" "php-fileinfo" "php-xml" "php-pecl-imagick" "php-pecl-geoip")
        for PHP_MODULE in "${PHP_MODULES[@]}"; do yum_ntd_install $PHP_MODULE "  - "; done
    fi

fi

# ------------------------------------------------

if $CFG_YUM_INSTALL_LINKCHECKER; then

    echo 'Install "linkchecker" ..'

    yum_install python2-pip "- install "
    yum_install python-devel "- install "
    yum_install gcc "- install "

    pip install --upgrade pip >/dev/null 2>&1
    pip install linkchecker >/dev/null 2>&1
    if [ $? -eq 0 ]; then echo "- OK "; else echo "- ERR #$?: " >&2; exit; fi
fi

# ------------------------------------------------

if $CFG_ADD_APACHE_TO_VIBUS_GROUP; then
    printf 'Add user "apache" to group "vibus"..'
    if [ "`cat /etc/group | grep "^vibus"`" == "" ]; then
        echo 'ERR: Group "vibus" not found'
    else
        if [ "`cat /etc/group | grep "^vibus" | grep "apache"`" == "" ]; then
            usermod -aG vibus apache >/dev/null 2>&1
            if [ $? -eq 0 ]; then echo "OK "; else echo "ERR #$?: " >&2; exit; fi
        else
            echo "ALREADY IN GROUP"
        fi
    fi
fi

# ------------------------------------------------

if $CFG_CREATE_HOSTNAME_DOMAIN_DIR; then

    mkdir -p /opt/vibus/sites/vibus/$DOMAIN/logs/{httpd,php-fpm}
    mkdir -p /opt/vibus/sites/vibus/$DOMAIN/public_html
    mkdir -p /opt/vibus/sites/vibus/$DOMAIN/secret
    mkdir -p /opt/vibus/sites/vibus/$DOMAIN/session
    mkdir -p /opt/vibus/sites/vibus/$DOMAIN/tmp

    chown -R vibus:vibus /opt/vibus/sites/vibus
    chmod -R 0660 /opt/vibus/sites/vibus/$DOMAIN/logs
    chmod -R 0750 /opt/vibus/sites/vibus/$DOMAIN/{public_html,secret,session,tmp}

fi

# ------------------------------------------------

if $CFG_FIREWALLD_CREATE_SERVER_ZONE; then
    if $CFG_FIREWALLD_SKIP_INTERFACES_MODIFY; then
        echo "========================================================="
        echo "!!! FIREWALLD"
        echo "!!! ------------------------------------------------------"
        echo "!!! AUTO MODIFY OF IFACE FILES DISABLED"
        echo "!!! YOU SHOULD ADD INSTRUCTIONS MANUALLY TO THE IFACES"
        echo "!!! CONFIGURATION FILES THAT LOCATED HERE:"
        echo "!!! /etc/sysconfig/network-scripts/iface-{NAME}"
        echo "========================================================="
    fi
fi
