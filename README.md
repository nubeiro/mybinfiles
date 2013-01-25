# My bin files

Utility scripts that live in my ~/bin. 


 - batcharge.py: See http://stevelosh.com/blog/2010/02/my-extravagant-zsh-prompt/#my-right-prompt-battery-capacity. Just a small tweak to make it work with acpi output on Linux.
 - mkvhost: create a virtual host for current dir. It creates a new apache virtualhost, enables it and adds an entry on /etc/hosts for the new servername. 
 - svnpackfiles.php: quick and dirty php script to create a zip file from subversion revisions. 
 - sf2init: give it a new project name and it will
  - create a folder for the new project, 
  - use composer to download symfony standard edition, 
  - sudo mkvhost (see above) to configure a virtualhost, 
  - sudo to fix permissions on app/cache, app/log 
   