<?php

/**
 * ResponsiveImage decorator for image class.
 * This is a helper for responsive design using the HTML5 <picture> tag OR <img srcset...>
 */
class ResponsiveImage extends ViewableData
{
    protected $cachedImage  = null;
    protected $method       = null;
    protected $methodW      = null;
    protected $methodH      = null;
    protected $extraClasses = "";
    protected $mediaQuery   = "";
    protected $owner        = null;

    public function __construct($owner, $mediaQuery) {
        $this->mediaQuery = $mediaQuery;
        $this->owner      = $owner;
    }

    public function forTemplate($field = null) {
        return $this->getTag();
    }

    /**
     * The new method provided on Image instances
     * @param boolean $mediaQuery This will become the `sizes` attribute on the image tag.
     * @param boolean $method     Optionally, the Image method to use for producing scaled versions,
     *                            e.g. SetWidth
     * @return Image_Responsive
     */
    public function Responsive($mediaQuery=false, $method=false, $methodW=null, $methodH=null, $extraClasses='')
    {
        if (!$this->owner) {
            throw new ResponsiveImageException("No owner for decorator ResponsiveImageDecorator");
        }
        if ($mediaQuery) {
            $this->setMediaQuery($mediaQuery);
        }
        if ($method) {
            $this->setSize($method, $methodW, $methodH);
        }
        if ($extraClasses) {
            $this->addExtraClasses($extraClasses);
        }
        return $this;
    }

    public function setMediaQuery($mediaQuery) {
        $this->mediaQuery = $mediaQuery;
        return $this;
    }

    public function setSize($method, $methodW, $methodH=0) {
        $this->method  = $method;
        $this->methodW = $methodW;
        $this->methodH = $methodH;
        return $this;
    }

    public function addExtraClasses($classes) {
        $this->extraClasses = $classes;
        return $this;
    }

    protected function getImage() {
        if ($this->cachedImage) {
            return $this->cachedImage;
        }

        try {
            // Allow use of Responsive(query, 'SetHeight', heightInPx)
            //
            if (strstr($this->method, 'Height')) {
                if ($this->methodH === null) {
                    $this->methodH = $this->methodW;
                }
                if ($this->owner->getHeight() == 0) {
                    throw new ResponsiveImageException('Source image has 0 height');
                }
                $aspectRatio   = $this->owner->getWidth() / $this->owner->getHeight();
                $this->methodW = $this->methodH * $aspectRatio;
            } elseif (strstr($this->method, 'Width')) {
                if ($this->owner->getHeight() == 0) {
                    throw new ResponsiveImageException('Source image has 0 height');
                }
                $aspectRatio   = $this->owner->getWidth() / $this->owner->getHeight();
                $this->methodH = $this->methodW / $aspectRatio;
            }

            $width  = $this->methodW ? $this->methodW : $this->owner->getWidth();
            $height = $this->methodH ? $this->methodH : $this->owner->getHeight();

            if ($height   === null || $width === null) {
                throw new ResponsiveImageException('Failed to obtain dimensions from owner image');
            }

            // Hacky: We create Image_Cached in order to then use the data
            // $image     = $this->owner->CroppedImage($smlW, $smlH);
            $image        = Image_Cached_Responsive::create($this->owner->Filename);
            $image->Title = $this->owner->Title;
            $image->setOriginal($this->owner, $width, $height);

            if ($this->method) {
                $image->setMethod($this->method);
            }

            if ($this->mediaQuery) {
                $image->setMediaQuery($this->mediaQuery);
            }
            $image->addExtraClasses($this->extraClasses);
            $this->cachedImage = $image;
            return $image;
        } catch (ResponsiveImageException $ex) {
            return null;
        }
    }

    public function getTag() {
        $image = $this->getImage();
        if ($image) {
            return $image->getTag();
        }
    }

    public function getOpenTag() {
        $image = $this->getImage();
        if ($image) {
            $html = $image->getOpenTag();
            return $html;
        }
    }

    public function getBackgroundAttr()
    {
        $image = $this->getImage();
        if ($image) {
            return $image->getBackgroundAttr();
        }
    }


    public function __call($method, $arguments)
    {
        $image = $this->getImage();
        if ($image && $image->hasMethod($method)) {
            return call_user_func_array(array($image, $method), $arguments);
        }
        throw new Exception("Undefined method $method called on ResponsiveImage with args: " . join(',', $arguments));
    }
}