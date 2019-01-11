<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 10/01/19
 * Time: 17:28
 */

namespace ILAB\MediaCloud\Tools\Storage;


class ResizeImage
{
    private $ext;
    private $image;
    private $newImage;
    private $origWidth;
    private $origHeight;
    private $resizeWidth;
    private $resizeHeight;
    /**
     * Class constructor requires to send through the image filename
     *
     * @param string $filename - Filename of the image you want to resize
     */
    public function __construct( $filename )
    {
        if(file_exists($filename))
        {
            $this->setImage( $filename );
        } else {
            throw new Exception('Image ' . $filename . ' can not be found, try another image.');
        }
    }
    /**
     * Set the image variable by using image create
     *
     * @param string $filename - The image filename
     */
    private function setImage( $filename )
    {
        $size = getimagesize($filename);
        $this->ext = $size['mime'];
        switch($this->ext)
        {
            case 'image/jpg':
            case 'image/jpeg':
                $this->image = imagecreatefromjpeg($filename);
                break;
            case 'image/gif':
                $this->image = @imagecreatefromgif($filename);
                break;
            case 'image/png':
                $this->image = @imagecreatefrompng($filename);
                break;
            default:
                throw new Exception("File is not an image, please use another file type.", 1);
        }
        $this->origWidth = imagesx($this->image);
        $this->origHeight = imagesy($this->image);
    }
    /**
     * Save the image as the image type the original image was
     *
     * @param  String[type] $savePath     - The path to store the new image
     * @param  string $imageQuality 	  - The qulaity level of image to create
     *
     */
    public function saveImage($savePath, $imageQuality="85", $download = false)
    {
        switch($this->ext)
        {
            case 'image/jpg':
            case 'image/jpeg':
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->newImage, $savePath, $imageQuality);
                }
                break;
            case 'image/gif':
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->newImage, $savePath);
                }
                break;
            case 'image/png':
                $invertScaleQuality = 9 - round(($imageQuality/100) * 9);
                if (imagetypes() & IMG_PNG) {
                    imagepng($this->newImage, $savePath, $invertScaleQuality);
                }
                break;
        }

        if($download)
        {
            header('Content-Description: File Transfer');
            header("Content-type: application/octet-stream");
            header("Content-disposition: attachment; filename= ".$savePath."");
            readfile($savePath);
        }
        imagedestroy($this->newImage);
    }
    /**
     * Resize the image to these set dimensions
     *
     * @param  int $path - file path
     *
     */
    public function resizeTo($path)
    {
        list($this->origWidth, $this->origHeight) = getimagesize($path);

        $percent = (1920.0 / $this->origWidth);

        $this->resizeWidth = $this->origWidth * $percent;
        $this->resizeHeight = $this->origHeight * $percent;

        $this->newImage = imagecreatetruecolor($this->resizeWidth, $this->resizeHeight);
        imagecopyresampled($this->newImage, $this->image, 0, 0, 0, 0, $this->resizeWidth, $this->resizeHeight, $this->origWidth, $this->origHeight);
    }

    public static function isBigger($path)
    {
        $width = getimagesize($path)[0];

        return $width <= 1920 ? false : true;
    }
}