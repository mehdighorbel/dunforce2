<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;

class AppParams
{
    protected $em;
    protected $targetDirectory;

    public function __construct(EntityManagerInterface $entityManager, $targetDirectory)
    {
        $this->em = $entityManager;
        $this->targetDirectory = $targetDirectory;
    }

    protected function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    public function getAllParams()
    {
        // recuperer tous les elements du fichier yaml
        $appParams = Yaml::parseFile($this->getTargetDirectory());
        return $appParams;
    }

    public function setAllParams($params)
    {
        // ecraser le contenu du fichier yaml
        $appParams = Yaml::dump($params);
        file_put_contents($this->getTargetDirectory(), $appParams);
    }
}