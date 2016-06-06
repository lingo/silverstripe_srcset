<?php

/**
 * Provide a ResponsiveImage method on the Image class which provides an img tag
 * with srcset attributes
 *
 * @author  Lucas Hudson <lucas@speak.geek.nz>
 * @package PPC
 */

/**
 * ResponsiveImage decorator for image class.
 * This is a helper for responsive design using the HTML5 <picture> tag OR <img srcset...>
 */
class ResponsiveImageDecorator extends DataExtension {
	/**
	 * This is used to calculate the smallest size image, based
	 * on the size of the original image.
	 * @var float
	 */
	private static $small_scaling_divisor = 4;

	/**
	 * The new method provided on Image instances
	 * @param boolean $mediaQuery This will become the `sizes` attribute on the image tag.
	 * @param boolean $method     Optionally, the Image method to use for producing scaled versions,
	 *                            e.g. SetWidth
	 * @return Image_Responsive
	 */
	public function ResponsiveImage($mediaQuery=false, $method=false) {
		$width        = $this->owner->getWidth();
		$height       = $this->owner->getHeight();
		if ($height   === null || $width === null) {
			return null;
		}

		$smlW = $width / self::$small_scaling_divisor;
		$smlH = $height / self::$small_scaling_divisor;

		// Hacky: We create Image_Cached in order to then use the data
		// $image     = $this->owner->CroppedImage($smlW, $smlH);
		$image        = Image_Responsive::create($this->owner->Filename);
		$image->Title = $this->owner->Title;
		$image->setOriginal($this->owner);
		if ($method) {
			$image->setMethod($method);
		}
		$image->setResponsiveDimensions($smlW, $smlH);

		if ($mediaQuery) {
			$image->setMediaQuery($mediaQuery);
		}
		return $image;
	}

	/**
	 * This is a debug method, and shouldn't ever show unless someone
	 * has instantiated the ResponsiveImage class directly.
	 */
	public function getTag() {
		return 'ResponsiveImage';
	}

}


/**
 * This is a fake 'cached' image which we use to return the correct HTML
 */
class Image_Responsive extends Image_Cached {

	private $smallWidth;
	private $smallHeight;
	private $maxWidth;
	private $maxHeight;
	private $medWidth;
	private $medHeight;
	private $mediaQuery;

	private $method = false;

	private static $default_method = 'SetWidth';

	/**
	 * How much larger the 'large' size image is than the minimum sizes passed to
	 * ResponsiveImage constructor
	 * @var integer
	 */

	private static $large_scaling_factor  = 4;
	/**
	 * How much larger the 'medium' size image is than the minimum sizes passed to
	 * ResponsiveImage constructor
	 * @var integer
	 */
	private static $medium_scaling_factor = 2;


	public function __construct($filename = null, $isSingleton = false) {
		parent::__construct($filename, $isSingleton);
	}

	public function setMediaQuery($query) {
		$this->mediaQuery = $query;
	}

	public function getMediaQuery() {
		return $this->mediaQuery;
	}

	/**
	 * Set the original Image source
	 * @param Image $original
	 */
	public function setOriginal($original) {
		$this->original = $original;
	}


	/**
	 * This is the method that actually calculates the sizes of all the versions
	 * of this image
	 * @param int $smlW Minimum width allowed for scaled copies.
	 * @param int $smlH Minimum height allowed for scaled copies.
	 */
	public function setResponsiveDimensions($smlW, $smlH) {
		$width     = $this->original->getWidth();
		$height    = $this->original->getHeight();

		if ($smlW == 0 || $smlW === min($smlW, $smlH)) {
			// calc smlH by aspect ratio
			$smlW = $width / $height * $smlH;
		} elseif ($smlH == 0 || $smlH === min($smlW, $smlH)) {
			// calc smlW by aspect ratio
			$smlH = $height / $width * $smlW;
		}

		$this->smallWidth  = intval($smlW);
		$this->smallHeight = intval($smlH);

		if ($this->method && strstr($this->method, 'Height')) {
			$maxWidth        = floor($height * $smlW / $smlH);
			$maxWidth        = min(self::$large_scaling_factor * $smlW, $maxWidth);
			$this->maxWidth  = min($this->original->getWidth(), $maxWidth);
			$this->maxHeight = min($this->smallHeight * self::$large_scaling_factor, $this->original->getHeight());
		} else {
			$maxHeight       = floor($width * $smlH / $smlW);
			$maxHeight       = min(self::$large_scaling_factor * $smlH, $maxHeight);
			$this->maxWidth  = min($this->smallWidth * self::$large_scaling_factor, $this->original->getWidth());
			$this->maxHeight = min($this->original->getHeight(), $maxHeight);
		}
		$this->medWidth    = intval(floor(($this->smallWidth  + $this->maxWidth)/2));
		$this->medHeight   = intval(floor(($this->smallHeight + $this->maxHeight/2)));
	}

	public function setMethod($method) {
		$this->method = $method;
	}

	public function getDefaultSourceWidth() {
		return floor($this->smallWidth) . 'w';
	}
	public function getMediumSourceWidth() {
		return floor($this->medWidth) . 'w';
	}
	public function getLargeSourceWidth() {
		return floor($this->maxWidth) . 'w';
	}

	public function getDefaultSourceHeight() {
		return floor($this->smallHeight) . 'h';
	}
	public function getMediumSourceHeight() {
		return floor($this->medHeight) . 'h';
	}
	public function getLargeSourceHeight() {
		return floor($this->maxHeight) . 'h';
	}

	public function getDefaultSource() {
		if (!$this->method) {
			$this->method = self::$default_method;
		}
		if (strstr($this->method, 'Height')) {
			return $this->original->getFormattedImage($this->method, $this->smallHeight)->URL;
		}
		return $this->original->getFormattedImage($this->method, $this->smallWidth, $this->smallHeight)->URL;
	}

	public function getMediumSource() {
		if (!$this->method) {
			$this->method = self::$default_method;
		}
		if (strstr($this->method, 'Height')) {
			return $this->original->getFormattedImage($this->method, $this->medHeight)->URL;
		}
		return $this->original->getFormattedImage($this->method, $this->medWidth, $this->medHeight)->URL;
	}

	public function getLargeSource() {
		if (!$this->method) {
			$this->method = self::$default_method;
		}
		if ($this->maxWidth == $this->original->getWidth()
			&& $this->maxHeight == $this->original->getHeight()) {
			return $this->original->getURL();
		}
		if (strstr($this->method, 'Height')) {
			return $this->original->getFormattedImage($this->method, $this->maxHeight)->URL;
		}
		return $this->original->getFormattedImage($this->method, $this->maxWidth, $this->maxHeight)->URL;
	}

	public function getTag() {
		// return '<b>test</b>';
		return $this->renderWith('ResponsiveImage');
	}

	public function getBackgroundAttr() {
		return $this->renderWith('ResponsiveImageBGAttr');
	}

	public function getWidth() {
		return $this->original ? $this->original->getWidth() : $this->smallWidth;
	}

	public function getHeight() {
		return $this->original ? $this->original->getHeight() : $this->smallHeight;
	}
}
