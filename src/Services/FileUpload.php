<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FileUpload {


    // Inject container interface
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function tryUpload($file, $object) {
        $filename = md5(uniqid()) . '.' . $file->guessExtension();
        if( $file->move($this->container->getParameter('dir_images'), $filename ) ){
            $object->setImage($filename);

            return true;
        }
    }

    public function tryDelete($object) {
            //Delete old file
            $uploadsDir = $this->container->getParameter('dir_images');
            $oldFile = $object->getImage();
            $dir = opendir($uploadsDir);
            while($oldf = readdir($dir)){
                if($oldf == $oldFile){
                    unlink($uploadsDir ."/". $oldFile);
                }
            }
            closedir($dir);
    }
}