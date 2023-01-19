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

### Block IP

Block IP
```
firewall-cmd --permanent --add-rich-rule="rule family='ipv4' source address='xxx.xxx.xxx.xxx' reject"
firewall-cmd --list-rich-rules 
```

Block IP with mask
```
firewall-cmd --permanent --zone=drop --add-source=xxx.xxx.xxx.0/24
firewall-cmd --list-all --zone=drop
```
