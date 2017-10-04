<?php

/**
 * Provide a ResponsiveImage method on the Image class which provides an img tag
 * with srcset attributes
 *
 * @author  Lucas Hudson <lucas@speak.geek.nz>
 */

class ResponsiveImageException extends Exception
{
}

class ResponsiveImageDecorator extends DataExtension
{
    public function Responsive($mediaQuery=false, $method=false, $methodW=null, $methodH=null, $extraClasses='')
    {
        return new ResponsiveImage($this->owner, $mediaQuery);
    }
}

