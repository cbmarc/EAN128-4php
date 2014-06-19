EAN128-4php
===========

EAN128-4php is a free barcode generator for php. It's very easy to use. This project is still in a very early stage of 
development, although it's already used in production. Error handling must be improved. All the information about Code128 
(EAN128) barcodes can be found here http://www.barcodeisland.com/code128.phtml.

Supported character Sets
------------------------

For now, the only supported character set is C. Although support for A and B character sets is planned for the near future.

Installation
-----------

Simply download the repository as a zip, unzip it and move the contents to the desired place in your project. Rename the folder 
to EAN128-4php and then include it:

```php
<?php include_once('EAN128-4php/EAN128-4php.php'); ?>
```

Usage
-----

Once you have installed EAN128-4php in your project you're ready to use it. This library can be used the way that best feets your needs:

1- Including EAN128-4php.php and just invoking it to create a file:

```php
<?php
  // This will save the image file with the generated barcode. Filename is specified
  $ean128 = new EAN1284php();
  $ean128->createImageFile($barcode, $filename);
?>
```

2- Without including the file, calling it directly from an img tag:

```php
<img src="EAN128-4php/EAN128-4php.php?barcode=<?php echo $barcode; ?>"/>
```

3- Without including the file, calling it directly but defined as an attachment (ideal if you want to download the image instead of showing it), you can also use this method inside an image tag:

```php
EAN128-4php/EAN128-4php.php?barcode=<?php echo $barcode; ?>&type=attach
```
