DM Shortee for Expression Engine 2
==================================

Short URLs can be generated from user-entered text, a template group/template combination or url from the Pages module. QR Codes are automatically generated for all short URLs

## Installation & configuration

### System requirements

Shortee requires

* EE2.1.3+
* PHP5 and Apache mod_rewrite or equivalent
* IP to Nation module installed
* You must also have a suitable short domain installed and running as a separate website

### Installing within EE

1. Download Shortee and extract the zip archive
2. Place the folder system/expressionengine/third_party/dm_shortee in your system/expressionengine/third_party folder
3. Place the folder themes/third_party/dm_shortee in your themes/third_party folder
4. From the EE Control Panel, install the Shortee Module.
5. Click on Shortee in the Module list, select Settings from the submenu and enter your short domain name including http://
6. Make a note of the Action ID from the Settings page

### Configuring short domain

1. Copy your index.php file from the root of your Expression Engine site to the web root of your short domain
2. Amend the $system_path variable to the full server path to your EE system folder. Beware open_basedir issues on certain servers.
3. Copy the .htaccess file from the short_domain folder of the Shortee zip archive to your short domain web root
4. Amend the XXX within the ‘ACT=XXX’ argument to the Action ID noted above