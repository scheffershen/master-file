<?php

namespace App\Twig\Extension;

use App\Entity\PSMFManagement\PSMF;
use App\Entity\LovManagement\Obligation;
use App\Entity\LovManagement\Scope;
use App\Manager\PSMFManagement\PSMFDocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    private $pSMFDocumentManager;
    private $kernel;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, PSMFDocumentManager $pSMFDocumentManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->pSMFDocumentManager = $pSMFDocumentManager;
        $this->kernel = $kernel;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('json_decode', 'json_decode'),
            new TwigFilter('mb_convert_encoding', 'mb_convert_encoding'),
            new TwigFilter('isImage', [$this, 'isImage']), 
            new TwigFilter('noUnderscore', [$this, 'noUnderscore']),
            new TwigFilter('highlights', [$this, 'highlights']),
            new TwigFilter('parser', [$this, 'parser']),
            new TwigFilter('titleParser', [$this, 'titleParser']),
            new TwigFilter('imageToBase64', [$this, 'imageToBase64']),
            new TwigFilter('basename', [$this, 'basename']),
            new TwigFilter('variable', [$this, 'variable']),
        ];
    }
    
    public function getFunctions()
    {
        return [
            new TwigFunction('missingVariables', [$this, 'missingVariables']),
        ];
    }

    public function missingVariables(?array $variables, PSMF $psmf): string
    {
        $result = ""; $_variables = [];
        foreach ($variables as $variable) {
            if ($variable->getScope()->getCode() == Scope::LOCALE && $variable->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                foreach ($variable->getCorrespondances() as $correspondance) {
                    if ($correspondance->getPsmf() == $psmf && empty($correspondance->getValueLocal())) {
                        $_variables[] = $variable;
                    }
                }
            }
        }
        
        return $result;
    }

    public function highlights(?string $contenu, ?string $balise): string
    {
        return str_replace($balise, '<span style="color:red">'.$balise.'</span>', $contenu);
    }

    public function variable(?string $balise, ?string $attribute)
    {
        $variable = $this->entityManager->getRepository("App\Entity\TemplateManagement\Variable")->findOneBy(['balise' => $balise]);
        if ($variable) {
            switch ($attribute) {
                case 'type':
                    return $variable->getType()->getTitle();
                    break;        
                case 'scope':  
                    return $variable->getScope();
                    break;                    
                case 'label':
                    return $variable->getLabel();
                    break;
                case 'balise':
                    return $variable->getBalise();
                    break;    
                case 'description':
                    return $variable->getDescription();
                    break;
                case 'userHelp':
                    return $variable->getUserHelp();
                    break;
                case 'isValid':
                    return $variable->getIsValid()? 'visible' : 'masquée';
                    break;
                case 'obligation':
                    return $variable->getObligation()->getTitle(); // 'oui':'non'
                    break;
                case 'classes':
                    return $variable->getClasses();
                    break;  
                default:    
                    return $attribute;
                    break;
            }
        }

        return $balise; 
    }

    public function isImage(?string $string): bool
    {
        if ($string) {
            $ext = pathinfo($string, PATHINFO_EXTENSION);
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                return true;
            }
        }
        return false;
    }

    public function imageToBase64(?string $valueLocal): string
    {
        if ($valueLocal) {
            $path = $this->kernel->getProjectDir().'/data/'.$valueLocal;
        } else {
            $path = $this->kernel->getProjectDir().'/data/not-found.png';
        }
        if (file_exists($path)) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            return $base64;
        }
        return '';
    }

    public function noUnderscore($string)
    {
        return str_replace("_", " ", $string);
    }

    public function parser($section)
    {
        $contenu = $this->pSMFDocumentManager->sectionParser($section, $section->getContenu());
        foreach ($section->getSections() as $subSection) {
            if ($subSection->getIsValid()) {
                $contenu .= $this->pSMFDocumentManager->sectionParser($subSection, $subSection->getContenu());
                foreach ($subSection->getSections() as $_subSection) {
                    if ($_subSection->getIsValid()) {
                        $contenu .= $this->pSMFDocumentManager->sectionParser($_subSection, $_subSection->getContenu());
                    }
                }                                
            }
        }
        $contenu = $this->pSMFDocumentManager->systemesParser(null, $contenu, PSMF::HTML);
        $contenu = $this->pSMFDocumentManager->globalesParser($contenu, PSMF::HTML, true);   
        $contenu = $this->pSMFDocumentManager->qualiosParser($contenu, true); 
        $contenu = $this->pSMFDocumentManager->localesParser(null, $contenu, PSMF::HTML);
        $contenu = $this->pSMFDocumentManager->logiqueParser(null, $contenu, PSMF::HTML); 

        return $contenu;  
    }

    public function titleParser($title)
    {
        $title = $this->pSMFDocumentManager->systemesParser(null, $title, PSMF::HTML);
        $title = $this->pSMFDocumentManager->globalesParser($title, PSMF::HTML, true);
        $title = $this->pSMFDocumentManager->qualiosParser($title, true); 
        $title = $this->pSMFDocumentManager->localesParser(null, $title, PSMF::HTML);
        $title = $this->pSMFDocumentManager->logiqueParser(null, $title, PSMF::HTML); 
        return $title;  
    }        

    /**
     * @var string $value
     * @return string
     */
    public function basename($value, $suffix = '')
    {
       return basename($value, $suffix);
    }
}
