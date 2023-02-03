# hestiacp-nodered
A plugin for Hestia Control Panel (via [hestiacp-pluginable](https://github.com/steveorevo/hestiacp-pluginable)) that enables hosting a Node-RED instance. With this plugin installed, user accounts can host their own Node-RED instance either in the root domain or as a subfolder installation. For instance, it is possible to run WordPress in the root domain while having Node-RED installed on the same domain in a subfolder (i.e. https://example.com/nodered); a perfect setup for creating solutions using [WPN](https://github.com/steveorevo/wpn). 

&nbsp;
> :warning: !!! Note: this repo is in progress; when completed, a release will appear in the release tab.

## Installation
HestiaCP-NodeRED requires an Ubuntu based installation of [Hestia Control Panel](https://hestiacp.com) in addition to an installation of [HestiaCP-Pluginable](https://github.com/steveorevo/hestiacp-pluginable) *and* [HesitaCP-NodeApp](https://github.com/steveorevo/hestiacp-nodeapp) to function; please ensure that you have first installed both Pluginable and NodeApp on your Hestia Control Panel before proceeding. Switch to a root user and simply download and unzip this project and move the folder to the `/usr/local/hestia/plugins` folder. It should appear as a subfolder with the name `nodered`, i.e. `/usr/local/hestia/plugins/nodered`.

Note: It is important that the plugin folder name is `nodered`.

```
sudo -s
cd /tmp
wget https://github.com/Steveorevo/hestiacp-nodered/archive/refs/heads/main.zip
unzip main.zip
mv hestiacp-nodered-main /usr/local/hestia/plugins/nodered
rm main.zip
```

Be sure to logout and login again to your Hestia Control Panel; the plugin will immediately start installing NodeJS and Node-RED depedencies in the background. A notification will appear under the admin user account indicating *"Node-RED plugin has finished installing"* when complete. This may take awhile before the options appear in Hestia. You can force manual installation via root level SSH:

```
sudo -s
cd /usr/local/hestia/plugins/nodered
./install
```
