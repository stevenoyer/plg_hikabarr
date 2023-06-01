<?php 

namespace Systrio\Plugins\Hikabarr\Helpers;

use Systrio\Plugins\Hikabarr\Dolibarr\Dolibarr;
use Systrio\Plugins\Hikabarr\Dolibarr\RequestApi;
use Systrio\Plugins\Hikabarr\Hikashop\Class\File;
use Systrio\Plugins\Hikabarr\Hikashop\Models\FileModel;

defined('_JEXEC') or die;

class ProductFileHelper
{
    private Dolibarr $client;

    public function __construct(Dolibarr $client)
    {
        $this->client = $client;
    }
    
    public function saveFiles(int $product_id): array
    {
        // Récupération des images Dolibarr
        $files_dolibarr = $this->client->requestApi->request(RequestApi::METHOD_GET, 'documents', ['modulepart' => 'product', 'id' => $product_id]);
        $files_dolibarr = json_decode($files_dolibarr);
        
        $files_ids = [];
        foreach ($files_dolibarr as $file)
        {
            // On défini un nouveau fichier modèle
            $fileHika = new FileModel;
            $fileHika->file_name = $file->name;
            //$fileHika->fullname_dolibarr = $file->fullname;

            // On télécharge les documents du produit
            $download = $this->client->requestApi->request(RequestApi::METHOD_GET, 'documents/download', ['modulepart' => 'product', 'original_file' => explode('/produit/', $file->fullname)[1]]);
            $download = json_decode($download);

            // On instancie la classe File avec le fichier, puis on téléverse le fichier
            $fileClass = new File($fileHika);
            $file_path = $fileClass->upload($download);

            // On défini le chemin du fichier
            $fileHika->file_path = $file_path;

            // Sauvegarde du fichier en base de données + récupération des IDs des fichiers
            $success = $fileClass->save();
            $files_ids[] = $success;
        }

        return $files_ids;
    }
    
}