#!/bin/bash

# User

USER=felix

# Jail directory

JD=./$USER

# =================================================

#umount $JD/proc || /bin/true
#umount $JD/dev|| /bin/true
#umount $JD/sys || /bin/true

umount $JD/dev/pts || /bin/true

rm -rf $JD

mkdir -p $JD

mkdir -p $JD/dev
mknod -m 666 $JD/dev/null c 1 3
mknod -m 666 $JD/dev/tty c 5 0
mknod -m 666 $JD/dev/zero c 1 5
mknod -m 666 $JD/dev/random c 1 8

# need this to avoid MC errors
mknod -m 666 $JD/dev/ptmx c 5 2
mkdir $JD/dev/pts
mount -t devpts -o gid=5,mode=620 /dev/pts $JD/dev/pts

mkdir -p $JD/bin

./cpwd.php /bin/bash $JD
./cpwd.php /bin/cat $JD
./cpwd.php /bin/chmod $JD
./cpwd.php /bin/clear $JD
./cpwd.php /bin/crontab $JD
./cpwd.php /bin/date $JD
./cpwd.php /bin/diff $JD
./cpwd.php /bin/echo $JD
./cpwd.php /bin/false $JD
./cpwd.php /bin/gunzip $JD
./cpwd.php /bin/htpasswd $JD
./cpwd.php /bin/ls $JD
./cpwd.php /bin/id $JD
./cpwd.php /bin/mcdiff $JD
./cpwd.php /bin/mcview $JD
./cpwd.php /bin/passwd $JD
./cpwd.php /bin/pwd $JD
./cpwd.php /bin/scp $JD
./cpwd.php /bin/sftp $JD
./cpwd.php /bin/ssh $JD
./cpwd.php /bin/tail $JD
./cpwd.php /bin/tee $JD

./cpwd.php /bin/whoami $JD
mkdir -p $JD/lib64
cp ./dep/whoami/lib64/* $JD/lib64/

./cpwd.php /bin/cd $JD
./cpwd.php /bin/chown $JD
./cpwd.php /bin/cp $JD
./cpwd.php /bin/curl $JD
./cpwd.php /bin/df $JD
./cpwd.php /bin/dir $JD
./cpwd.php /bin/env $JD
./cpwd.php /bin/grep $JD
./cpwd.php /bin/gzip $JD
./cpwd.php /bin/ln $JD

./cpwd.php /bin/mc $JD
mkdir -p $JD/usr/share
cp -R /usr/share/mc $JD/usr/share/

./cpwd.php /bin/mcedit $JD
./cpwd.php /bin/mv $JD
./cpwd.php /bin/mv $JD
./cpwd.php /bin/php $JD
./cpwd.php /bin/rm $JD
./cpwd.php /bin/screen $JD
./cpwd.php /bin/size $JD
./cpwd.php /bin/ssh-keygen $JD
./cpwd.php /bin/tar $JD
./cpwd.php /bin/touch $JD

mkdir -p $JD/etc
#cat /etc/passwd | grep "^root\|^$USER" > $JD/etc/passwd
#cat /etc/group | grep "^root\|^$USER" > $JD/etc/group

cp /etc/passwd $JD/etc/passwd
cp /etc/group $JD/etc/group

cp /etc/php.ini $JD/etc/php.ini

cp /etc/inputrc $JD/etc/inputrc

cp /etc/nsswitch.conf $JD/etc/nsswitch.conf

mkdir $JD/tmp
chown root:$USER $JD/tmp
chmod 0775 $JD/tmp

mkdir -p $JD/home/$USER
chown root:$USER $JD/home/$USER
chmod 0770 $JD/home/$USER
cp ./dep/.bash_profile $JD/home/$USER/
cp /etc/inputrc $JD/home/$USER/.inputrc

mkdir -p $JD/usr/share
cp -R /usr/share/mc $JD/usr/share/

#mkdir -p $JD/proc
#mount -o bind /proc $JD/proc

#mkdir -p $JD/dev
#mount -o bind /dev $JD/dev 

#mkdir -p $JD/sys
#mount -o bind /sys $JD/sys
