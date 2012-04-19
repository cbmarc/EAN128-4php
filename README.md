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

Once you have installed EAN128-4php in your project you're ready to use it. This library is built as a module (not as a class). 
There are two options for basic usage:

```php
<?php
  // This will download directly the generated barcode, without saving it (allowing you to load it directly in an img)
  createImageBuffer($barcode);
  
  // This will save the image file with the generated barcode. Filename is specified
  createImageFile($barcode, $filename);
?>
```

As you see, there are two options which allow you to directly download the image or save it. Note that with the createImageBuffer 
function you can directly load the image in a img html tag:

```php
<img src="<?php createImageBuffer($barcode); ?>" alt="barcode" />
```