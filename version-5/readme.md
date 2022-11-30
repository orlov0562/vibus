# Shinjiru [DRAFT]

By default Firewalld turned On and only 80 (HTTP) and SSH port enabled.
```
  # firewall-cmd --state
running
  
# firewall-cmd --list-all
public (active)
  target: default
  icmp-block-inversion: no
  interfaces: venet0
  sources: 
  services: cockpit dhcpv6-client ssh
  ports: 80/tcp 20223/tcp
  protocols: 
  forward: no
  masquerade: no
  forward-ports: 
  source-ports: 
  icmp-blocks: 
  rich rules: 
  
# firewall-cmd --get-active-zones
public
  interfaces: venet0
```

To turn on HTTPS you need to enable it manually

```
# firewall-cmd --zone=public --permanent --add-service=https
success
    
# firewall-cmd --reload
success

  # firewall-cmd --list-all
public (active)
  target: default
  icmp-block-inversion: no
  interfaces: venet0
  sources: 
  services: cockpit dhcpv6-client ssh https
  ports: 80/tcp 20223/tcp
  protocols: 
  forward: no
  masquerade: no
  forward-ports: 
  source-ports: 
  icmp-blocks: 
  rich rules: 
```
