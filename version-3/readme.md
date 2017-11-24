# Usage

```bash
yum install -y wget
wget https://raw.githubusercontent.com/orlov0562/vibus/master/version-3/install.sh
bash install.sh
```
# Known problems

If script said that it "can't found interfaces that has UP status", then you can set option CFG_FIREWALLD_SKIP_INTERFACES_MODIFY to true
```text
CFG_FIREWALLD_SKIP_INTERFACES_MODIFY=true
```
and rerun script, after execution complete you should manually add 
```text
ZONE=webserver
```
to external interfaces configuration files

you can find all interfaces like this
```bash
ifconfig -a
```

You will get something like this:

>**eth0**  Link encap:Ethernet  HWaddr 54:04:a6:3f:49:fb  
>          inet addr:192.168.100.196  Bcast:192.168.100.255  Mask:255.255.255.0


and then found configuration file, ex

>/etc/sysconfig/network-scripts/ifcfg-**eth0**

and just add to the end of this file line
```bash
ZONE=webserver
```
after this if you want to restart whole server just make network restart
```bash
systemctl restart network firewalld
```
