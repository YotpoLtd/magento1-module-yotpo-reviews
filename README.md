Magento Yotpo Extension
=============================

This library includes the files of the Yotpo Reviews extension
The directories hierarchy is as positioned in a standard magento project library

This library will also include different version packages as magento extensions

Note
----


Requirements
------------

- magento 1.4 + 


Installation
------------

To install the extension on your magento:

1. On your magento admin panel, go to System -> Magento Connect -> Magento Connect Manager

2. Upload the yotporeviews-[version_num].tgz file and click on upload



Usage
-----

After the installation, Go to The magento admin panel

Go to System -> Configuration, and click on Yotpo Social Reviews Software on the left sidebar

Insert Your account app key and secret

To insert the widget manually on your product page add the following code in the file [magento-root-directory]/app/design/frontend/[your-theme]/default/template/catalog/product/view.phtml

```
<?php $this->helper('yotpo')->showWidget($this, $_product); ?>
```

To insert the bottom line on product pages, add the following code in the file [magento-root-directory]/app/design/frontend/[your-theme]/default/template/catalog/product/list.phtml

```
<?php $this->helper('yotpo')->showBottomline($this, $_product); ?>
```

The full extension manuals can be found here:

https://support.yotpo.com/en/article/magento-installing-yotpo
https://support.yotpo.com/en/article/magento-configuring-yotpo-after-installation




 


