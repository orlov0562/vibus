# LinkChecker
- site: http://wummel.github.io/linkchecker/index.html
- main: http://wummel.github.io/linkchecker/man1/linkchecker.1.html

# Run with timeout from cli example

```bash
timeout -sHUP 1m linkchecker http://site.com
timeout -sINT 1m linkchecker http://site.com
```

# SIGNALS

**Common kill signal**

SIGHUP 	1 	Hangup
SIGINT 	2 	Interrupt from keyboard
SIGKILL 	9 	Kill signal (never graceful)
SIGTERM 	15 	Termination signal
SIGSTOP 	17,19,23 	Stop the process 
