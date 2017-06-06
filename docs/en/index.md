# Srcset module

This allows you to easily use the `HTML5` `srcset` attribute with your SilverStripe assets, so you can simply output responsive images in your templates.

## Installation

See the README for install instructions

## Usage

By default this module will add the `ResponsiveImageDecorator` to the `Image` class, so it's enabled by default.

Then in your templates

```ss
$MyImage.Responsive()
```

## Customizing

You can override the default templates used, by creating your own `ResponsiveImage.ss` or `ResponsiveImageBGAttr.ss` templates in your theme.

Also you can set configuration options in `YML`.
These scaling factors determine how the various sizes are calculated from the image's original size.

```yml
Image_Responsive:
    small_scaling_factor:  0.25
    medium_scaling_factor: .5
```


## Requirements
You will probably want to require the *respimage* script.  This provides a polyfill for older browsers that don't natively support `srcset`.
A version of this script is included in this module.  Simply require it somewhere

```php
Requirements::javascript('silverstripe_srcset/node_modules/respimage/respimage.min.js');
```

**OR**

```ss
<% require javascript(silverstripe_srcset/node_modules/respimage/respimage.min.js) %>
```
