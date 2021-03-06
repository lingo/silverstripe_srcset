<?php



/**
 * This is a fake 'cached' image which we use to return the correct HTML
 */
class Image_Cached_Responsive extends Image_Cached
{
    private $tinyWidth;
    private $tinyHeight;
    private $smallWidth;
    private $smallHeight;
    private $maxWidth;
    private $maxHeight;
    private $medWidth;
    private $medHeight;
    private $mediaQuery;
    protected $extraClassNames;


    private $method = false;

    private static $default_method = 'SetWidth';


    /**
     * Maximum size in pixels of tiny image longest side
     * @var integer
     */
    private static $tiny_max_size = 64;


    /**
     * How much smaller the 'small' size image is than the original image size
     * @var integer
     */
    private static $small_scaling_factor  = 0.25;

    /**
     * How much smaller the 'medium' size image is than the original image size
     * @var integer
     */
    private static $medium_scaling_factor = 0.5;


    public function __construct($filename = null, $isSingleton = false)
    {
        parent::__construct($filename, $isSingleton);
    }

    public function setMediaQuery($query)
    {
        $this->mediaQuery = $query;
    }

    public function getMediaQuery()
    {
        return $this->mediaQuery;
    }

    /**
     * Set the original Image source
     * @param Image $original
     */
    public function setOriginal($original, $width, $height)
    {
        $this->original = $original;
        $this->calcResponsiveDimensions($width, $height);
    }

    protected static function image_to_data_url($image)
    {
        $absoluteFilename = BASE_PATH . DIRECTORY_SEPARATOR . $image->Filename;
        if (!file_exists($absoluteFilename)) {
            return false;
        }
        $imageData = file_get_contents($absoluteFilename);
        if ($imageData === false) {
            return false;
        }
        // Read image path, convert to base64 encoding
        $imageData = base64_encode($imageData);
        // Format the image SRC:  data:{mime};base64,{data};
        $src = 'data:'.mime_content_type($absoluteFilename).';base64,'.$imageData;
        return $src;
    }

    /**
     * This is the method that actually calculates the sizes of all the versions
     * of this image
     * @param int $width width to use for scaled/cropped images
     * @param int $height height to use for scaled/cropped images
     */
    public function calcResponsiveDimensions($width, $height)
    {
        if (!$this->original) {
            throw new ResponsiveImageException("No original image exists for Image_Cached_Responsive, ensure you call setOriginal first");
        }
        if (!($width && $height)) {
            // throw new ResponsiveImageException("0 pixel dimension source image");
        }

        $aspectRatio = ($height !== 0) ? $width / $height : 1;

        $this->maxWidth  = $width;
        $this->maxHeight = $height;

        $small_scaling_factor  = $this->config()->get('small_scaling_factor');
        $medium_scaling_factor = $this->config()->get('medium_scaling_factor');

        if (strstr($this->getMethod(), 'Height')) {
            $this->tinyHeight  = min(self::$tiny_max_size, $height * ($small_scaling_factor/2));
            $this->tinyWidth   = $this->tinyHeight * $aspectRatio;

            $this->smallHeight = round($height * $small_scaling_factor);
            $this->smallWidth  = round($this->smallHeight * $aspectRatio);

            $this->smallHeight = round($height * $small_scaling_factor);
            $this->smallWidth  = round($this->smallHeight * $aspectRatio);

            $this->medHeight   = round($height * $medium_scaling_factor);
            $this->medWidth    = round($this->medHeight * $aspectRatio);
        } else {
            $this->tinyWidth   = min(self::$tiny_max_size, $width * ($small_scaling_factor/2));
            $this->tinyHeight  = $this->tinyWidth / $aspectRatio;

            $this->smallWidth  = $width * $small_scaling_factor;
            $this->smallHeight = round($this->smallWidth / $aspectRatio);

            $this->medWidth    = round($width * $medium_scaling_factor);
            $this->medHeight   = round($this->medWidth / $aspectRatio);
        }
    }


    public function getMethod()
    {
        return $this->method ? $this->method : self::$default_method;
    }

    /**
     * Set the method that will be used to generate formatted images
     * @see  Image::generateFormattedImage
     * @param string $method  e.g. SetWidth,CroppedImage,SetHeight etc.
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }


    public function getSmallSourceWidth()
    {
        return floor($this->smallWidth) . 'w';
    }
    public function getMediumSourceWidth()
    {
        return floor($this->medWidth) . 'w';
    }
    public function getLargeSourceWidth()
    {
        return floor($this->maxWidth) . 'w';
    }

    public function getSmallSourceHeight()
    {
        return floor($this->smallHeight) . 'h';
    }
    public function getMediumSourceHeight()
    {
        return floor($this->medHeight) . 'h';
    }
    public function getLargeSourceHeight()
    {
        return floor($this->maxHeight) . 'h';
    }

    public function getTinySourceAttributes()
    {
        $image = $this->getTinyBlurredImage();
        if ($image) {
            $dataURI = self::image_to_data_url($image);
            if ($dataURI !== false) {
                return <<<HTML
        style="background-image: url({$dataURI}); background-repeat: no-repeat; background-size: cover;"
HTML;
            }
        }
    }

    public function getTinyBlurredSource()
    {
        if ($this->getTinyBlurredImage()) {
            return $this->getTinyBlurredImage()->URL;
        }
    }

    public function getTinyBlurredSourceDataURI()
    {
        if ($this->getTinyBlurredImage()) {
            $dataURI = self::image_to_data_url($this->getTinyBlurredImage());
            return $dataURI;
        }
    }


    public function getTinyBlurredImage()
    {
        $image = null;
        $args  = null;

        if ($this->method === 'FillMax') {
            $args  = array('Fill', $this->tinyWidth, $this->tinyHeight);
            $image = $this->original->FillMax($this->tinyWidth, $this->tinyHeight);
        } elseif (strstr($this->method, 'Height')) {
            $args  = array($this->getMethod(), $this->tinyHeight);
            $image = $this->original->getFormattedImage($this->getMethod(), $this->tinyHeight);
        } else {
            $args  = array($this->getMethod(), $this->tinyWidth, $this->tinyHeight);
            $image = $this->original->getFormattedImage($this->getMethod(), $this->tinyWidth, $this->tinyHeight);
        }

        if ($image) {
            $backend = Injector::inst()->createWithArgs(
                Image::config()->backend,
                array(
                    Director::baseFolder()."/" . $image->Filename,
                    $args
                )
            );
            if ($backend && $backend->hasImageResource()) {
                $imageRsrc = $backend->getImageResource();
                if (imagefilter($imageRsrc, IMG_FILTER_GAUSSIAN_BLUR, 8)) {
                    $backend->writeTo(BASE_PATH . DIRECTORY_SEPARATOR . $image->Filename);
                }
            }
            return $image;
        }
    }

    public function getSmallSource()
    {
        $image = null;
        if ($this->method === 'FillMax') {
            $image = $this->original->FillMax($this->smallWidth, $this->smallHeight);
        } elseif (strstr($this->method, 'Height')) {
            $image = $this->original->getFormattedImage($this->getMethod(), $this->smallHeight);
        } else {
            $image = $this->original->getFormattedImage($this->getMethod(), $this->smallWidth, $this->smallHeight);
        }
        if ($image) {
            return $image->URL;
        }
    }

    public function getMediumSource()
    {
        $image = null;
        if ($this->method === 'FillMax') {
            $image = $this->original->FillMax($this->medWidth, $this->medHeight);
        } elseif (strstr($this->method, 'Height')) {
            $image = $this->original->getFormattedImage($this->getMethod(), $this->medHeight);
        } else {
            $image = $this->original->getFormattedImage($this->getMethod(), $this->medWidth, $this->medHeight);
        }
        if ($image) {
            return $image->URL;
        }
    }

    public function getLargeSource()
    {
        $image = null;

        if ($this->maxWidth == $this->original->getWidth()
            && $this->maxHeight == $this->original->getHeight()) {
            return $this->original->getURL();
        }

        if ($this->method === 'FillMax') {
            $image = $this->original->FillMax($this->maxWidth, $this->maxHeight);
        } elseif (strstr($this->method, 'Height')) {
            $image = $this->original->getFormattedImage($this->getMethod(), $this->maxHeight);
        } else {
            $image = $this->original->getFormattedImage($this->getMethod(), $this->maxWidth, $this->maxHeight);
        }
        if ($image) {
            return $image->URL;
        }
    }

    public function getTag()
    {
        // return '<b>test</b>';
        return $this->renderWith('ResponsiveImage');
    }

    public function getBackgroundAttr()
    {
        /**
         * IF we render with source_file_comments enabled, then this breaks tags due to html comments within a tag.
         * So, we temporarily disable, in case.
         */
        $commentsEnabled = Config::inst()->get('SSViewer', 'source_file_comments');
        Config::inst()->update('SSViewer', 'source_file_comments', false);

        $html = $this->renderWith('ResponsiveImageBGAttr');
        Config::inst()->update('SSViewer', 'source_file_comments', $commentsEnabled);
        return $html;
    }

    public function getWidth()
    {
        return $this->original ? $this->original->getWidth() : $this->smallWidth;
    }

    public function getHeight()
    {
        return $this->original ? $this->original->getHeight() : $this->smallHeight;
    }

    public function addExtraClasses($classNamesSeparatedBySpaces)
    {
        $this->extraClassNames = $classNamesSeparatedBySpaces;
    }

    public function getExtraClasses()
    {
        return $this->extraClassNames;
    }

    public function getOpenTag() {
        $commentsEnabled = Config::inst()->get('SSViewer', 'source_file_comments');
        Config::inst()->update('SSViewer', 'source_file_comments', false);

        $html = $this->renderWith('ResponsiveImage_TagOpen');

        Config::inst()->update('SSViewer', 'source_file_comments', $commentsEnabled);
        return $html;


    }
}
