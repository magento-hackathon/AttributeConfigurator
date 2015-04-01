AttributeConfigurator
=====================
The AttributeConfigurator enables you to centralize the versioning of your attributes in one file (compared to have
them scaattered over several update scripts).

Warning
-------
Work Draft. Currently nonworking.

Facts
-----
- version: 0.1.0
- extension key: Aoe_AttributeConfigurator
- [extension on GitHub](https://github.com/AOEpeople/AttributeConfigurator)
- no workflow implemented right now, only supportive functionality was developed

Description
-----------
The AttributeConfigurator enables you to centralize the versioning of your attributes in one file (compared to have
them scaattered over several update scripts).

Configuration is found at System/Configuration/Catalog/Catalog/Attribute Configurator.

Import Path is created at Magento Base Director with var/importexport/product_attributes. Change this path in
the config if this doesn´t suit your needs - but use only var as it is some kind of spool file.

Requirements
------------
- PHP >= 5.2.0
- Mage_Core

Compatibility
-------------
- Magento >= 1.7

Installation Instructions
-------------------------
Use the included modman manifest to integrate into your project. If you want to manually copy the files, use the directory structure provided at /src.

Uninstallation
--------------
1. Remove all extension files from your Magento installation

Planned Features
----------------
- supply an XML File with attribute information (name, label, type, values/options, ...)
- add new attributes
- change existing one (type change also, but in some cases information loss is inevitable)
- clean up no longer needed ones
- just care about a specific subset that comes/came from XML (handled by flagging) to not interfere with system or 3rd party attributes

Support
-------
If you have any issues with this extension, open an issue on [GitHub](https://github.com/AOEpeople/AttributeConfigurator/issues).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Nils Preuss
Rico Neitzel
Joachim Adomeit

License
-------
[GNU GPL v3.0](http://www.gnu.org/licenses/gpl-3.0.txt)

Copyright
---------
(c) 2014 Firegento
Rework 2015 Aoe
