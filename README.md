# SilverStripe Responsive Images

Easily use responsive images in SilverStripe templates, via the HTML5 `img`
tag's `srcset` attribute.

Browser compatibility for this attribute can be seen here
[caniuse](http://caniuse.com/#search=srcset), but in any case a
[polyfill](https://github.com/aFarkas/respimage) is provided (use `npm
install`) for older browsers.

See also  [the full documentation](docs/en/index.md)

## Installation

### Via composer

```sh
composer require lingo/silverstripe_respimage
```

## Usage

Simply call the `Responsive` method on your image. You should provide a media
query as an argument.  [See here](https://ericportis.com/posts/2014/srcset-sizes/) for explanation of this attribute.

```ss
<% if $FeaturedImage %>
	$FeaturedImage.Responsive("(max-width: 800px) 93vw, 45vw")
<% end_if %>
```

### CSS

You'll probably want to have a bit of CSS also set up to make images scale nicely, something like this:

```css

img {
	width:  auto;
	height: 100%;
}

```

## More information

You can also use the `.BackgroundAttr` method if you need to add an inline
style attribute to an element, in order to use a responsive background image.

