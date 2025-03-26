# hcpp-nodered
A plugin for Hestia Control Panel (via [hestiacp-pluginable](https://github.com/virtuosoft-dev/hestiacp-pluginable) and [hcpp-nodeapp](https://github.com/virtuosoft-dev/hcpp-nodeapp)) that enables hosting a Node-RED instance. With this plugin installed, a new Quick Installer option will appear. *[Note: User account must have SSH/bash set per NodeApp's instructions for this plugin to work](https://github.com/virtuosoft-dev/hcpp-nodeapp#using-nodeapp-to-host-a-nodejs-website)*. should have User accounts can host their own Node-RED instance either in the root domain or as a subfolder installation. For instance, it is possible to run WordPress in the root domain while having Node-RED installed on the same domain in a subfolder (i.e. https://example.com/nodered); a perfect setup for creating solutions using [WPN](https://virtuosoft.com/wpn).

## Installation
HCPP-NodeRED requires an Ubuntu or Debian based installation of [Hestia Control Panel](https://hestiacp.com) in addition to an installation of [HestiaCP-Pluginable](https://github.com/virtuosoft-dev/hestiacp-pluginable) *and* [HCPP-NodeApp](https://github.com/virtuosoft-dev/hcpp-nodeapp) to function; please ensure that you have first installed pluginable on your Hestia Control Panel before proceeding. Clone the latest release version (i.e. replace **v2.1.0** below with the latest release version) to the nodered folder:

```
cd /usr/local/hestia/plugins
sudo git clone --branch v2.1.0 https://github.com/virtuosoft-dev/hcpp-nodered nodered
```

Note: It is important that the destination plugin folder name is `nodered`.

Be sure to logout and login again to your Hestia Control Panel as the admin user or, as admin, visit Server (gear icon) -> Configure -> Plugins -> Save; the plugin will immediately start installing depedencies in the background. 

A notification will appear under the admin user account indicating *"Node-RED plugin has finished installing"* when complete. This may take awhile before the options appear in Hestia. You can force manual installation via:

```
cd /usr/local/hestia/plugins/nodered
sudo ./install
sudo touch "/usr/local/hestia/data/hcpp/installed/nodered"
```


## Support the creator
You can help this author's open source development endeavors by donating any amount to Stephen J. Carnam @ Virtuosoft. Your donation, no matter how large or small helps pay for essential time and resources to create MIT and GPL licensed projects that you and the world can benefit from. Click the link below to donate today :)
<div>
         

[<kbd> <br> Donate to this Project <br> </kbd>][KBD]


</div>


<!---------------------------------------------------------------------------->

[KBD]: https://virtuosoft.com/donate

https://virtuosoft.com/donate