<?php

namespace app\models;

use yii\base\BaseObject;

class DownloadJob extends BaseObject implements \yii\queue\JobInterface
{
    public $url;
    public $file;
    
    public function execute($queue)
    {
        //file_put_contents($this->file, file_get_contents($this->url));
        $nuevoFichero = fopen('nuevo.csv', 'w+');
    }
}
?>