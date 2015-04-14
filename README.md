AttributeConfigurator
=====================
The AttributeConfigurator enables you to centralize the versioning of your attributes in one XML File. Goal of this
Module is to have one File to modify if you want to change Attributes and not search through a lot of Magento
Update Scripts to find the latest change to your Attribute.

Warning
-------
Work Draft. Currently nonworking.
Generally, tampering with EAV Attribute Data is not without risk. We recommend backing up your Database regularly.

Facts
-----
- version: 0.1.0
- extension key: Aoe_AttributeConfigurator
- [extension on GitHub](https://github.com/AOEpeople/AttributeConfigurator)

Description
-----------
For an example XML, have a look at the attributes.xml in the /etc Directory of the extension. If you don´t understand
the settings we recommend a Blogpost from Ben Marks that explains basics for the eav_attribute Table Settings:
[Magento EAV Attribute Setup](http://www.webguys.de/magento/eav-attribute-setup/)

Configuration is found at System/Configuration/Catalog/Catalog/Attribute Configurator.
Import Path is read relative to the Magento 'var' Directory.

The Configurator is run by a shellskript: /shell/aoe_attribute_import.php

Run it like this: php aoe_attribute_import.php --runAll

Notice that the Extension does not change any Attributes that were added via Update Scripts or Third Party Extensions
for your own safety. Attributes maintained by the Configurator are marked with a is_maintained_by_configurator - Flag.

Requirements
------------
- PHP >= 5.2.0
- Mage_Core

Compatibility
-------------
- Magento >= 1.7

Installation Instructions
-------------------------
Use the included modman manifest to integrate into your project. If you want to manually copy the files, use the
directory structure provided at /src.

Uninstallation
--------------
1. Remove all extension files from your Magento installation
2. Drop the 'is_maintained_by_configurator' Column in the 'eav_attribute' Table.
3. Removing created Attributes depends on your Setup and will probably need manual action

Planned Features
----------------
- Change existing Attributes (type change also, but in some cases information loss is inevitable if source and target
type are incompatible, i.e. text to integer)
- Clean up no longer needed Attributes

Support
-------
If you have any issues with this extension, open an issue on
[GitHub](https://github.com/AOEpeople/AttributeConfigurator/issues).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a
[pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Nils Preuss<br />
Rico Neitzel<br />
Thomas Neumann<br />
Joachim Adomeit<br />

License
-------
[GNU GPL v3.0](http://www.gnu.org/licenses/gpl-3.0.txt)

Copyright
---------
(c) 2014 Firegento
Rework 2015 Aoe
