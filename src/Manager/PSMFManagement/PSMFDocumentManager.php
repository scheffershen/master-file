<?php

namespace App\Manager\PSMFManagement;

use App\Entity\LovManagement\Gender;
use App\Entity\LovManagement\Obligation;
use App\Entity\LovManagement\Scope;
use App\Entity\LovManagement\TypeVariable;
use App\Entity\PSMFManagement\PSMF;
use App\Entity\QualiosManagement\Document;
use App\Entity\TemplateManagement\Section;
use App\Entity\TemplateManagement\Template;
use App\Entity\TemplateManagement\Correspondance;
use App\Entity\TemplateManagement\Variable;
use App\Library\HtmlToDoc;
use App\Library\HtmlToPdfConverter;
use App\Repository\QualiosManagement\DocumentRepository;
use App\Repository\PSMFManagement\PSMFHistoryRepository;
use App\Repository\TemplateManagement\CorrespondanceRepository;
use App\Repository\TemplateManagement\SectionRepository;
use App\Repository\TemplateManagement\VariableRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;

/**
 * PSMFDocument Manager
 */
class PSMFDocumentManager
{
    private $requestStack;
    private $variableRepository;
    private $sectionRepository;
    private $correspondanceRepository;
    private $kernel;
    private $slugger;
    private $twig;
    private $converter;
    private $documentRepository;

    public function __construct(RequestStack $requestStack, VariableRepository $variableRepository, CorrespondanceRepository $correspondanceRepository, SectionRepository $sectionRepository, PSMFHistoryRepository $pSMFHistoryRepository, DocumentRepository $documentRepository, KernelInterface $kernel, SluggerInterface $slugger, Environment $twig, HtmlToPdfConverter $converter)
    {
        $this->requestStack = $requestStack;
        $this->variableRepository = $variableRepository;
        $this->sectionRepository = $sectionRepository;
        $this->pSMFHistoryRepository = $pSMFHistoryRepository;
        $this->correspondanceRepository = $correspondanceRepository;
        $this->documentRepository = $documentRepository;
        $this->kernel = $kernel;
        $this->slugger = $slugger;
        $this->twig = $twig;
        $this->converter = $converter;
    }

    /*
    * get psmf local correspondance
    */
    public function correspondanceLocale(PSMF $psmf, Variable $variable): ?string
    {
        if ($variable->getScope()->getCode() == Scope::LOCALE) {
            foreach ($variable->getCorrespondances() as $correspondance) {
                if ($correspondance->getPsmf() == $psmf && !empty($correspondance->getValueLocal())) {
                    if (is_file($this->kernel->getProjectDir() . '/data/' . $correspondance->getValueLocal())) {
                        return '<img src="' . $this->kernel->getProjectDir() . '/data/' . $correspondance->getValueLocal() . '"/>';
                    } else {
                        return $correspondance->getValueLocal();
                    }
                }
            }
        }
        return $variable->getScope()->getCode();
    }

    /*
    * number of missing local correspondances by a psmf
    */
    public function correspondanceLocaleMissing(PSMF $psmf): ?int
    {
        $total_obligation = $nb_correspondanceLocale = 0;
        $variables = $this->variableRepository->findEquivalencesLocales();
        foreach ($variables as $variable) {
            if ($variable->getScope()->getCode() == Scope::LOCALE && $variable->getObligation()->getCode() == Obligation::OBLIGATOIRE && $variable->isValid()) {
                $total_obligation++;
                foreach ($variable->getCorrespondances() as $correspondance) {
                    if ($correspondance->getPsmf() == $psmf && !empty($correspondance->getValueLocal())) {
                        $nb_correspondanceLocale++;
                    }
                }
            }
        }
        return $total_obligation - $nb_correspondanceLocale;
    }

    /*
    * missing number of globale correspondances
    */
    public function correspondanceGlobaleMissing(): ?int
    {
        $total_obligation = $nb_correspondanceGlobale = 0;
        $variables = $this->variableRepository->findEquivalencesGlobales();
        foreach ($variables as $variable) {
            if ($variable->getScope()->getCode() == Scope::GLOBALE && $variable->getObligation()->getCode() == Obligation::OBLIGATOIRE && $variable->isValid()) {
                $total_obligation++;
                foreach ($variable->getCorrespondances() as $correspondance) {
                    if (!empty($correspondance->getValueLocal())) {
                        $nb_correspondanceGlobale++;
                    }
                }
            }
        }
        return $total_obligation - $nb_correspondanceGlobale;
    }

    /*
    * le parser section par section
    */
    public function sectionParser(?Section $section, string $contenu): string
    {
        $systemes = $this->variableRepository->findEquivalencesSystemes();
        foreach ($systemes as $systeme) {
            switch ($systeme->getBalise()) {
                case 'SYS_PART_TITRE':
                    if ($section) {
                        $contenu = str_replace('SYS_PART_TITRE', $section->getTitle(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_PART_TITRE', '<span style="color:red">SYS_PART_TITRE</span>', $contenu);
                    }
                    break;
                case 'SYS_PART_POSITION':
                    if ($section) {
                        // if ($section->getParent()) {
                        //     $contenu = str_replace('SYS_PART_POSITION', $section->getParent()->getPosition().'.'.$section->getPosition(), $contenu);
                        // } else {
                        //     $contenu = str_replace('SYS_PART_POSITION', $section->getPosition(), $contenu);
                        // }
                        $contenu = str_replace('SYS_PART_POSITION', '', $contenu);
                    } else {
                        $contenu = str_replace('SYS_PART_POSITION', '<span style="color:red">SYS_PART_POSITION</span>', $contenu);
                    }
                    break;
            }
        }
        return $contenu;
    }

    /*
    * table contents Parser for templateController
    */
    public function tableContentsParser(iterable $sections, string $contenu, bool $parser = true): string
    {
        if ($parser) {
            $tableContents =  $this->twig->render(
                'PSMFManagement/PSMF/draft/table_contents.html.twig',
                [
                    'sections' => $sections
                ]
            );
            return str_replace('SYS_TABLE_CONTENTS', $tableContents, $contenu);
        } else {
            return str_replace('SYS_TABLE_CONTENTS', '<span style="color:red">SYS_TABLE_CONTENTS</span>', $contenu);
        }
    }

    private function getLogiqueParserBalise(): array
    {
        return ['_if_um_euqppv_', '_if_presta_euqppv_', '_if_client_euqppv_', '_if_eu_deputy_', '_else_', '_endif_', '_if_UM_ONLY_PV_PROVIDER_', '_if_UM_NOT_ONLY_PV_PROVIDER_', '_if_CLINET_BBAC_', '_if_NOT_CLINET_BBAC_', '_if_PV_DATABASE_EVEREPORT_', '_if_PV_DATABASE_SAFETY_EASY_', '_if_PV_DATABASE_OTHER_', '_if_UM_EU_QPPV_BACKUP_', '_if_OTHER_EU_QPPV_BACKUP_', '_if_UM_CALL_MANAGEMENT_WORKING_HOURS_', '_if_OTHER_CALL_MANAGEMENT_WORKING_HOURS_', '_if_UM_CALL_MANAGEMENT_NON_WORKING_HOURS_', '_if_OTHER_CALL_MANAGEMENT_NON_WORKING_HOURS_', '_if_UM_CASE_MANAGEMENT_', '_if_OTHER_CASE_MANAGEMENT_', '_if_UM_ICSR_SUBMISSION_', '_if_OTHER_ICSR_SUBMISSION_', '_if_UM_LITERATURE_MONITORING_', '_if_OTHER_LITERATURE_MONITORING_', '_if_UM_SIGNAL_MANAGEMENT_', '_if_OTHER_SIGNAL_MANAGEMENT_', '_if_UM_PSUR_MANAGEMENT_', '_if_OTHER_PSUR_MANAGEMENT_', '_if_UM_RMP_MANAGEMENT_', '_if_OTHER_RMP_MANAGEMENT_', '_if_UM_VARIATION_MANAGEMENT_', '_if_OTHER_VARIATION_MANAGEMENT_', '_if_UM_RESPONSES_TO_THE_AUTHORITIES_', '_if_OTHER_RESPONSES_TO_THE_AUTHORITIES_', '_if_UM_COMMUNICATION_OF_SAFETY_CONCERNS_', '_if_OTHER_COMMUNICATION_OF_SAFETY_CONCERNS_', '_if_LOCAL_QPPV_AUSTRIA_NUL_', '_if_LOCAL_QPPV_AUSTRIA_UM_', '_if_LOCAL_QPPV_AUSTRIA_OTHER_', '_if_LOCAL_QPPV_GERMANY_NUL_', '_if_LOCAL_QPPV_GERMANY_UM_', '_if_LOCAL_QPPV_GERMANY_OTHER_', '_if_LOCAL_QPPV_POLAND_NUL_', '_if_LOCAL_QPPV_POLAND_UM_', '_if_LOCAL_QPPV_POLAND_OTHER_', '_if_LOCAL_QPPV_BELGIUM_NUL_', '_if_LOCAL_QPPV_BELGIUM_UM_', '_if_LOCAL_QPPV_BELGIUM_OTHER_', '_if_LOCAL_QPPV_GREECE_NUL_', '_if_LOCAL_QPPV_GREECE_UM_', '_if_LOCAL_QPPV_GREECE_OTHER_', '_if_LOCAL_QPPV_PORTUGAL_NUL_', '_if_LOCAL_QPPV_PORTUGAL_UM_', '_if_LOCAL_QPPV_PORTUGAL_OTHER_', '_if_LOCAL_QPPV_BULGARIA_NUL_', '_if_LOCAL_QPPV_BULGARIA_UM_', '_if_LOCAL_QPPV_BULGARIA_OTHER_', '_if_LOCAL_QPPV_HUNGARY_NUL_', '_if_LOCAL_QPPV_HUNGARY_UM_', '_if_LOCAL_QPPV_HUNGARY_OTHER_', '_if_LOCAL_QPPV_ROMANIA_NUL_', '_if_LOCAL_QPPV_ROMANIA_UM_', '_if_LOCAL_QPPV_ROMANIA_OTHER_', '_if_LOCAL_QPPV_CROATIA_NUL_', '_if_LOCAL_QPPV_CROATIA_UM_', '_if_LOCAL_QPPV_CROATIA_OTHER_', '_if_LOCAL_QPPV_SOUTHERN_NUL_', '_if_LOCAL_QPPV_SOUTHERN_UM_', '_if_LOCAL_QPPV_SOUTHERN_OTHER_', '_if_LOCAL_QPPV_IRELAND_NUL_', '_if_LOCAL_QPPV_IRELAND_UM_', '_if_LOCAL_QPPV_IRELAND_OTHER_', '_if_LOCAL_QPPV_SLOVAKIA_NUL_', '_if_LOCAL_QPPV_SLOVAKIA_UM_', '_if_LOCAL_QPPV_SLOVAKIA_OTHER_', '_if_LOCAL_QPPV_CYPRUS_NUL_', '_if_LOCAL_QPPV_CYPRUS_UM_', '_if_LOCAL_QPPV_CYPRUS_OTHER_', '_if_LOCAL_QPPV_ITALY_NUL_', '_if_LOCAL_QPPV_ITALY_UM_', '_if_LOCAL_QPPV_ITALY_OTHER_', '_if_LOCAL_QPPV_SLOVENIA_NUL_', '_if_LOCAL_QPPV_SLOVENIA_UM_', '_if_LOCAL_QPPV_SLOVENIA_OTHER_', '_if_LOCAL_QPPV_CZECH-REPUBLIC_NUL_', '_if_LOCAL_QPPV_CZECH-REPUBLIC_UM_', '_if_LOCAL_QPPV_CZECH-REPUBLIC_OTHER_', '_if_LOCAL_QPPV_LATVIA_NUL_', '_if_LOCAL_QPPV_LATVIA_UM_', '_if_LOCAL_QPPV_LATVIA_OTHER_', '_if_LOCAL_QPPV_SPAIN_NUL_', '_if_LOCAL_QPPV_SPAIN_UM_', '_if_LOCAL_QPPV_SPAIN_OTHER_', '_if_LOCAL_QPPV_DENMARK_NUL_', '_if_LOCAL_QPPV_DENMARK_UM_', '_if_LOCAL_QPPV_DENMARK_OTHER_', '_if_LOCAL_QPPV_LITHUANIA_NUL_', '_if_LOCAL_QPPV_LITHUANIA_UM_', '_if_LOCAL_QPPV_LITHUANIA_OTHER_', '_if_LOCAL_QPPV_SWEDEM_NUL_', '_if_LOCAL_QPPV_SWEDEM_UM_', '_if_LOCAL_QPPV_SWEDEM_OTHER_', '_if_LOCAL_QPPV_ESTONIA_NUL_', '_if_LOCAL_QPPV_ESTONIA_UM_', '_if_LOCAL_QPPV_ESTONIA_OTHER_', '_if_LOCAL_QPPV_LUXEMBOURG_NUL_', '_if_LOCAL_QPPV_LUXEMBOURG_UM_', '_if_LOCAL_QPPV_LUXEMBOURG_OTHER_', '_if_LOCAL_QPPV_UNITED-KINGDOM_NUL_', '_if_LOCAL_QPPV_UNITED-KINGDOM_UM_', '_if_LOCAL_QPPV_UNITED-KINGDOM_OTHER_', '_if_LOCAL_QPPV_FINLAND_NUL_', '_if_LOCAL_QPPV_FINLAND_UM_', '_if_LOCAL_QPPV_FINLAND_OTHER_', '_if_LOCAL_QPPV_NETHERLAND_NUL_', '_if_LOCAL_QPPV_NETHERLAND_UM_', '_if_LOCAL_QPPV_NETHERLAND_OTHER_', '_if_LOCAL_QPPV_FRANCE_NUL_', '_if_LOCAL_QPPV_FRANCE_UM_', '_if_LOCAL_QPPV_FRANCE_OTHER_', '_if_LOCAL_QPPV_MALTA_NUL_', '_if_LOCAL_QPPV_MALTA_UM_', '_if_LOCAL_QPPV_MALTA_OTHER_', '_if_LOCAL_QPPV_ICELAND_NUL_', '_if_LOCAL_QPPV_ICELAND_UM_', '_if_LOCAL_QPPV_ICELAND_OTHER_', '_if_LOCAL_QPPV_NORWAY_NUL_', '_if_LOCAL_QPPV_NORWAY_UM_', '_if_LOCAL_QPPV_NORWAY_OTHER_'];
    }

    /*
    * psmf logique et option contents Parser
    */
    public function logiqueParser(?PSMF $psmf, string $contenu): string
    {
        if ($psmf) {
            $contenu = preg_replace('/_if_um_euqppv_/i', '{% if psmf.euqppvEntity.code == "um" %}', $contenu);
            $contenu = preg_replace('/_if_presta_euqppv_/i', '{% if psmf.euqppvEntity.code == "presta" %}', $contenu);
            $contenu = preg_replace('/_if_client_euqppv_/i', '{% if psmf.euqppvEntity.code == "client" %}', $contenu);
            $contenu = preg_replace('/_if_eu_deputy_/i', '{% if psmf.deputyEUQPPV %}', $contenu);
            $contenu = preg_replace('/_else_/i', '{% else %}', $contenu);
            $contenu = preg_replace('/_endif_/i', '{% endif %}', $contenu);

            // version 1.1.0     
            // psmf.hasOtherPVProviders  
            $contenu = preg_replace('/_if_UM_ONLY_PV_PROVIDER_/i', '{% if psmf.hasOtherPVProviders is not null and psmf.hasOtherPVProviders == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_NOT_ONLY_PV_PROVIDER_/i', '{% if psmf.hasOtherPVProviders is not null and psmf.hasOtherPVProviders == true %}', $contenu);

            // psmf.isOldClientBbac 
            $contenu = preg_replace('/_if_CLINET_BBAC_/i', '{% if psmf.isOldClientBbac is not null and psmf.isOldClientBbac == true %}', $contenu);
            $contenu = preg_replace('/_if_NOT_CLINET_BBAC_/i', '{% if psmf.isOldClientBbac is not null and psmf.isOldClientBbac == false %}', $contenu);

            // psmf.basePV
            $contenu = preg_replace('/_if_PV_DATABASE_EVEREPORT_/i', "{% if psmf.basePV is not null and psmf.basePV.code == 'EVEREPORT' %}", $contenu);
            $contenu = preg_replace('/_if_PV_DATABASE_SAFETY_EASY_/i', "{% if psmf.basePV is not null and psmf.basePV.code == 'SAFETY_EASY' %}", $contenu);
            $contenu = preg_replace('/_if_PV_DATABASE_OTHER_/i', "{% if psmf.basePV is not null and psmf.basePV.code == 'OTHER' %}", $contenu);

            // psmf.activitesUM
            $contenu = preg_replace('/_if_UM_EU_QPPV_BACKUP_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("EU_QPPV_BACKUP") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_EU_QPPV_BACKUP_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("EU_QPPV_BACKUP") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_CALL_MANAGEMENT_WORKING_HOURS_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("CALL_MANAGEMENT_WORKING_HOURS") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_CALL_MANAGEMENT_WORKING_HOURS_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("CALL_MANAGEMENT_WORKING_HOURS") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_CALL_MANAGEMENT_NON_WORKING_HOURS_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("CALL_MANAGEMENT_NON_WORKING_HOURS") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_CALL_MANAGEMENT_NON_WORKING_HOURS_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("CALL_MANAGEMENT_NON_WORKING_HOURS") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_CASE_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("CASE_MANAGEMENT") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_CASE_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("CASE_MANAGEMENT") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_ICSR_SUBMISSION_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("ICSR_SUBMISSION") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_ICSR_SUBMISSION_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("ICSR_SUBMISSION") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_LITERATURE_MONITORING_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("LITERATURE_MONITORING") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_LITERATURE_MONITORING_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("LITERATURE_MONITORING") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_SIGNAL_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("SIGNAL_MANAGEMENT") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_SIGNAL_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("SIGNAL_MANAGEMENT") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_PSUR_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("PSUR_MANAGEMENT") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_PSUR_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("PSUR_MANAGEMENT") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_RMP_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("RMP_MANAGEMENT") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_RMP_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("RMP_MANAGEMENT") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_VARIATION_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("VARIATION_MANAGEMENT") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_VARIATION_MANAGEMENT_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("VARIATION_MANAGEMENT") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_RESPONSES_TO_THE_AUTHORITIES_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("RESPONSES_TO_THE_AUTHORITIES") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_RESPONSES_TO_THE_AUTHORITIES_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("RESPONSES_TO_THE_AUTHORITIES") == false %}', $contenu);
            $contenu = preg_replace('/_if_UM_COMMUNICATION_OF_SAFETY_CONCERNS_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("COMMUNICATION_OF_SAFETY_CONCERNS") == true %}', $contenu);
            $contenu = preg_replace('/_if_OTHER_COMMUNICATION_OF_SAFETY_CONCERNS_/i', '{% if psmf.activitesUM|length > 0 and psmf.hasActiviteUM("COMMUNICATION_OF_SAFETY_CONCERNS") == false %}', $contenu);

            // psmf.localQPPVPays et psmf.localQPPVUM
            $contenu = preg_replace('/_if_LOCAL_QPPV_AUSTRIA_NUL_/i', '{% if psmf.hasLocalQPPVUM("AUSTRIA") == false and psmf.hasLocalQPPVOther("AUSTRIA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_AUSTRIA_UM_/i', '{% if psmf.hasLocalQPPVUM("AUSTRIA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_AUSTRIA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("AUSTRIA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_GERMANY_NUL_/i', '{% if psmf.hasLocalQPPVUM("GERMANY") == false and psmf.hasLocalQPPVOther("GERMANY") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_GERMANY_UM_/i', '{% if psmf.hasLocalQPPVUM("GERMANY") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_GERMANY_OTHER_/i', '{% if psmf.hasLocalQPPVOther("GERMANY") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_POLAND_NUL_/i', '{% if psmf.hasLocalQPPVUM("POLAND") == false and psmf.hasLocalQPPVOther("POLAND") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_POLAND_UM_/i', '{% if psmf.hasLocalQPPVUM("POLAND") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_POLAND_OTHER_/i', '{% if psmf.hasLocalQPPVOther("POLAND") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_BELGIUM_NUL_/i', '{% if psmf.hasLocalQPPVUM("BELGIUM") == false and psmf.hasLocalQPPVOther("BELGIUM") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_BELGIUM_UM_/i', '{% if psmf.hasLocalQPPVUM("BELGIUM") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_BELGIUM_OTHER_/i', '{% if psmf.hasLocalQPPVOther("BELGIUM") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_GREECE_NUL_/i', '{% if psmf.hasLocalQPPVUM("GREECE") == false and psmf.hasLocalQPPVOther("GREECE") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_GREECE_UM_/i', '{% if psmf.hasLocalQPPVUM("GREECE") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_GREECE_OTHER_/i', '{% if psmf.hasLocalQPPVOther("GREECE") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_PORTUGAL_NUL_/i', '{% if psmf.hasLocalQPPVUM("PORTUGAL") == false and psmf.hasLocalQPPVOther("PORTUGAL") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_PORTUGAL_UM_/i', '{% if psmf.hasLocalQPPVUM("PORTUGAL") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_PORTUGAL_OTHER_/i', '{% if psmf.hasLocalQPPVOther("PORTUGAL") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_BULGARIA_NUL_/i', '{% if psmf.hasLocalQPPVUM("PORTUGAL") == false and psmf.hasLocalQPPVOther("BULGARIA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_BULGARIA_UM_/i', '{% if psmf.hasLocalQPPVUM("BULGARIA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_BULGARIA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("BULGARIA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_HUNGARY_NUL_/i', '{% if psmf.hasLocalQPPVUM("HUNGARY") == false and psmf.hasLocalQPPVOther("HUNGARY") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_HUNGARY_UM_/i', '{% if psmf.hasLocalQPPVUM("HUNGARY") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_HUNGARY_OTHER_/i', '{% if psmf.hasLocalQPPVOther("HUNGARY") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_ROMANIA_NUL_/i', '{% if psmf.hasLocalQPPVUM("ROMANIA") == false and psmf.hasLocalQPPVOther("ROMANIA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_ROMANIA_UM_/i', '{% if psmf.hasLocalQPPVUM("ROMANIA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_ROMANIA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("ROMANIA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_CROATIA_NUL_/i', '{% if psmf.hasLocalQPPVUM("CROATIA") == false and psmf.hasLocalQPPVOther("CROATIA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_CROATIA_UM_/i', '{% if psmf.hasLocalQPPVUM("CROATIA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_CROATIA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("CROATIA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_SOUTHERN_NUL_/i', '{% if psmf.hasLocalQPPVUM("SOUTHERN") == false and psmf.hasLocalQPPVOther("SOUTHERN") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SOUTHERN_UM_/i', '{% if psmf.hasLocalQPPVUM("SOUTHERN") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SOUTHERN_OTHER_/i', '{% if psmf.hasLocalQPPVOther("SOUTHERN") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_IRELAND_NUL_/i', '{% if psmf.hasLocalQPPVUM("IRELAND") == false and psmf.hasLocalQPPVOther("IRELAND") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_IRELAND_UM_/i', '{% if psmf.hasLocalQPPVUM("IRELAND") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_IRELAND_OTHER_/i', '{% if psmf.hasLocalQPPVOther("IRELAND") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_SLOVAKIA_NUL_/i', '{% if psmf.hasLocalQPPVUM("SLOVAKIA") == false and psmf.hasLocalQPPVOther("SLOVAKIA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SLOVAKIA_UM_/i', '{% if psmf.hasLocalQPPVUM("SLOVAKIA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SLOVAKIA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("SLOVAKIA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_CYPRUS_NUL_/i', '{% if psmf.hasLocalQPPVUM("CYPRUS") == false and psmf.hasLocalQPPVOther("CYPRUS") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_CYPRUS_UM_/i', '{% if psmf.hasLocalQPPVUM("CYPRUS") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_CYPRUS_OTHER_/i', '{% if psmf.hasLocalQPPVOther("CYPRUS") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_ITALY_NUL_/i', '{% if psmf.hasLocalQPPVUM("ITALY") == false and psmf.hasLocalQPPVOther("ITALY") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_ITALY_UM_/i', '{% if psmf.hasLocalQPPVUM("ITALY") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_ITALY_OTHER_/i', '{% if psmf.hasLocalQPPVOther("ITALY") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_SLOVENIA_NUL_/i', '{% if psmf.hasLocalQPPVUM("SLOVENIA") == false and psmf.hasLocalQPPVOther("SLOVENIA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SLOVENIA_UM_/i', '{% if psmf.hasLocalQPPVUM("SLOVENIA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SLOVENIA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("SLOVENIA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_CZECH-REPUBLIC_NUL_/i', '{% if psmf.hasLocalQPPVUM("CZECH-REPUBLIC") == false and psmf.hasLocalQPPVOther("CZECH-REPUBLIC") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_CZECH-REPUBLIC_UM_/i', '{% if psmf.hasLocalQPPVUM("CZECH-REPUBLIC") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_CZECH-REPUBLIC_OTHER_/i', '{% if psmf.hasLocalQPPVOther("CZECH-REPUBLIC") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_LATVIA_NUL_/i', '{% if psmf.hasLocalQPPVUM("LATVIA") == false and psmf.hasLocalQPPVOther("LATVIA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_LATVIA_UM_/i', '{% if psmf.hasLocalQPPVUM("LATVIA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_LATVIA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("LATVIA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_SPAIN_NUL_/i', '{% if psmf.hasLocalQPPVUM("SPAIN") == false and psmf.hasLocalQPPVOther("SPAIN") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SPAIN_UM_/i', '{% if psmf.hasLocalQPPVUM("SPAIN") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SPAIN_OTHER_/i', '{% if psmf.hasLocalQPPVOther("SPAIN") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_DENMARK_NUL_/i', '{% if psmf.hasLocalQPPVUM("DENMARK") == false and psmf.hasLocalQPPVOther("DENMARK") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_DENMARK_UM_/i', '{% if psmf.hasLocalQPPVUM("DENMARK") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_DENMARK_OTHER_/i', '{% if psmf.hasLocalQPPVOther("DENMARK") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_LITHUANIA_NUL_/i', '{% if psmf.hasLocalQPPVUM("LITHUANIA") == false and psmf.hasLocalQPPVOther("LITHUANIA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_LITHUANIA_UM_/i', '{% if psmf.hasLocalQPPVUM("LITHUANIA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_LITHUANIA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("LITHUANIA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_SWEDEM_NUL_/i', '{% if psmf.hasLocalQPPVUM("SWEDEM") == false and psmf.hasLocalQPPVOther("SWEDEM") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SWEDEM_UM_/i', '{% if psmf.hasLocalQPPVUM("SWEDEM") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_SWEDEM_OTHER_/i', '{% if psmf.hasLocalQPPVOther("SWEDEM") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_ESTONIA_NUL_/i', '{% if psmf.hasLocalQPPVUM("ESTONIA") == false and psmf.hasLocalQPPVOther("ESTONIA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_ESTONIA_UM_/i', '{% if psmf.hasLocalQPPVUM("ESTONIA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_ESTONIA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("ESTONIA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_LUXEMBOURG_NUL_/i', '{% if psmf.hasLocalQPPVUM("LUXEMBOURG") == false and psmf.hasLocalQPPVOther("LUXEMBOURG") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_LUXEMBOURG_UM_/i', '{% if psmf.hasLocalQPPVUM("LUXEMBOURG") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_LUXEMBOURG_OTHER_/i', '{% if psmf.hasLocalQPPVOther("LUXEMBOURG") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_UNITED-KINGDOM_NUL_/i', '{% if psmf.hasLocalQPPVUM("UNITED-KINGDOM") == false and psmf.hasLocalQPPVOther("UNITED-KINGDOM") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_UNITED-KINGDOM_UM_/i', '{% if psmf.hasLocalQPPVUM("UNITED-KINGDOM") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_UNITED-KINGDOM_OTHER_/i', '{% if psmf.hasLocalQPPVOther("UNITED-KINGDOM") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_FINLAND_NUL_/i', '{% if psmf.hasLocalQPPVUM("FINLAND") == false and psmf.hasLocalQPPVOther("FINLAND") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_FINLAND_UM_/i', '{% if psmf.hasLocalQPPVUM("FINLAND") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_FINLAND_OTHER_/i', '{% if psmf.hasLocalQPPVOther("FINLAND") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_NETHERLAND_NUL_/i', '{% if psmf.hasLocalQPPVUM("NETHERLAND") == false and psmf.hasLocalQPPVOther("NETHERLAND") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_NETHERLAND_UM_/i', '{% if psmf.hasLocalQPPVUM("NETHERLAND") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_NETHERLAND_OTHER_/i', '{% if psmf.hasLocalQPPVOther("NETHERLAND") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_FRANCE_NUL_/i', '{% if psmf.hasLocalQPPVUM("FRANCE") == false and psmf.hasLocalQPPVOther("FRANCE") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_FRANCE_UM_/i', '{% if psmf.hasLocalQPPVUM("FRANCE") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_FRANCE_OTHER_/i', '{% if psmf.hasLocalQPPVOther("FRANCE") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_MALTA_NUL_/i', '{% if psmf.hasLocalQPPVUM("MALTA") == false and psmf.hasLocalQPPVOther("MALTA") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_MALTA_UM_/i', '{% if psmf.hasLocalQPPVUM("MALTA") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_MALTA_OTHER_/i', '{% if psmf.hasLocalQPPVOther("MALTA") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_ICELAND_NUL_/i', '{% if psmf.hasLocalQPPVUM("ICELAND") == false and psmf.hasLocalQPPVOther("ICELAND") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_ICELAND_UM_/i', '{% if psmf.hasLocalQPPVUM("ICELAND") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_ICELAND_OTHER_/i', '{% if psmf.hasLocalQPPVOther("ICELAND") == true %}', $contenu);

            $contenu = preg_replace('/_if_LOCAL_QPPV_NORWAY_NUL_/i', '{% if psmf.hasLocalQPPVUM("NORWAY") == false and psmf.hasLocalQPPVOther("NORWAY") == false %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_NORWAY_UM_/i', '{% if psmf.hasLocalQPPVUM("NORWAY") == true %}', $contenu);
            $contenu = preg_replace('/_if_LOCAL_QPPV_NORWAY_OTHER_/i', '{% if psmf.hasLocalQPPVOther("NORWAY") == true %}', $contenu);
        } else {
            //$contenu = str_replace('{% if psmf.deputyEUQPPV %}', '<span style="color:green">{% if psmf.deputyEUQPPV %}</span>', $contenu); 
            foreach ($this->getLogiqueParserBalise() as $balise) {
                //$contenu = str_replace($balise, '<span style="color:green">'.$balise.'</span>', $contenu); 
                // pas différencier les majuscules et minuscules
                $contenu = preg_replace('/' . $balise . '/i', '<span style="color:green;font-style:italic;">' . $balise . '</span>', $contenu);
            }
        }
        return $contenu;
    }

    /*
    * le parser globale de systemes
    */
    public function systemesParser(?PSMF $psmf, string $contenu, string $format): string
    {
        $systemes = $this->variableRepository->findEquivalencesSystemes();
        $request = $this->requestStack->getCurrentRequest();

        foreach ($systemes as $systeme) {
            switch (trim($systeme->getBalise())) {
                case 'SYS_CLIENT_ADRESS':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CLIENT_ADRESS', $psmf->getClient()->getAdress(), $contenu);
                    } else { // tempalteController preview
                        $contenu = str_replace('SYS_CLIENT_ADRESS', '<span style="color:red">SYS_CLIENT_ADRESS</span>', $contenu);
                    }
                    break;
                case 'SYS_CLIENT_LOGO':
                    if ($psmf) {
                        if ($format == PSMF::PDF) {
                            if ($psmf->getClient()->getLogoUri() && is_file($this->kernel->getProjectDir() . '/data/large/' . $psmf->getClient()->getLogoUri())) {
                                $contenu = str_replace('SYS_CLIENT_LOGO', '<img src="' . $this->kernel->getProjectDir() . '/data/large/' . $psmf->getClient()->getLogoUri() . '"/>', $contenu);
                            } else {
                                $contenu = str_replace('SYS_CLIENT_LOGO', '', $contenu);
                            }
                        } else {
                            if ($format == PSMF::WORD) {
                                if ($psmf->getClient()->getLogoUri() && is_file($this->kernel->getProjectDir() . '/data/large/' . $psmf->getClient()->getLogoUri())) {
                                    $contenu = str_replace('SYS_CLIENT_LOGO', '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/large/' . $psmf->getClient()->getLogoUri() . '"/>', $contenu);
                                } else {
                                    $contenu = str_replace('SYS_CLIENT_LOGO', '', $contenu);
                                }
                            } else {
                                if ($psmf->getClient()->getLogoUri() && is_file($this->kernel->getProjectDir() . '/data/large/' . $psmf->getClient()->getLogoUri())) {
                                    $contenu = str_replace('SYS_CLIENT_LOGO', '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/large/' . $psmf->getClient()->getLogoUri() . '"/>', $contenu);
                                } else {
                                    $contenu = str_replace('SYS_CLIENT_LOGO', '', $contenu);
                                }
                            }
                        }
                    } else {
                        $contenu = str_replace('SYS_CLIENT_LOGO', '<span style="color:red">SYS_CLIENT_LOGO</span>', $contenu);
                    }
                    break;
                case 'SYS_CLIENT_NAME':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CLIENT_NAME', $psmf->getClient()->getName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CLIENT_NAME', '<span style="color:red">SYS_CLIENT_NAME</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_ADRESS':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_ADRESS', $psmf->getContactPvClient()->getAdresse(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_ADRESS', '<span style="color:red">SYS_CONTACT_PV_CLIENT_ADRESS</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_COMPANY':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_COMPANY', $psmf->getContactPvClient()->getWorkName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_COMPANY', '<span style="color:red">SYS_CONTACT_PV_CLIENT_COMPANY</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_CV':
                    if ($psmf) {
                        if ($psmf->getContactPvClient()->getCvUri()) {
                            if ($format == PSMF::PDF) {
                                if (is_file($this->kernel->getProjectDir() . '/data/' . $psmf->getContactPvClient()->getCvUri())) {
                                    $contenu = str_replace('SYS_CONTACT_PV_CLIENT_CV', '<img src="' . $this->kernel->getProjectDir() . '/data/' . $psmf->getContactPvClient()->getCvUri() . '"/>', $contenu);
                                } else {
                                    $contenu = str_replace('SYS_CONTACT_PV_CLIENT_CV', '<img src="' . $this->kernel->getProjectDir() . '/data/' . PSMF::NOT_FOUND_IMAGE . '"/>', $contenu);
                                }
                            } else {
                                if ($format == PSMF::WORD) {
                                    $contenu = str_replace('SYS_CONTACT_PV_CLIENT_CV', '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $psmf->getContactPvClient()->getCvUri() . '"/>', $contenu);
                                } else {
                                    $contenu = str_replace('SYS_CONTACT_PV_CLIENT_CV', '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $psmf->getContactPvClient()->getCvUri() . '"/>', $contenu);
                                }
                            }
                        } else {
                            $contenu = str_replace('SYS_CONTACT_PV_CLIENT_CV', '', $contenu);
                        }
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_CV', '<span style="color:red">SYS_CONTACT_PV_CLIENT_CV</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_FAX':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_FAX', $psmf->getContactPvClient()->getFax(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_FAX', '<span style="color:red">SYS_CONTACT_PV_CLIENT_FAX</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_FIRSTNAME':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_FIRSTNAME', $psmf->getContactPvClient()->getFirstName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_FIRSTNAME', '<span style="color:red">SYS_CONTACT_PV_CLIENT_FIRSTNAME</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_FIX':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_FIX', $psmf->getContactPvClient()->getFixe(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_FIX', '<span style="color:red">SYS_CONTACT_PV_CLIENT_FIX</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_FUNCTION':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_FUNCTION', $psmf->getContactPvClient()->getWorkFunction(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_FUNCTION', '<span style="color:red">SYS_CONTACT_PV_CLIENT_FUNCTION</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_HE_SHE':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_HE_SHE', ($psmf->getContactPvClient()->getGender()->getCode() == Gender::HOMME) ? 'he' : 'she', $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_HE_SHE', '<span style="color:red">SYS_CONTACT_PV_CLIENT_HE_SHE</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_HIM_HER':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_HIM_HER', ($psmf->getContactPvClient()->getGender()->getCode() == Gender::HOMME) ? 'him' : 'her', $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_HIM_HER', '<span style="color:red">SYS_CONTACT_PV_CLIENT_HIM_HER</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_HIS_HER':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_HIS_HER', ($psmf->getContactPvClient()->getGender()->getCode() == Gender::HOMME) ? 'his' : 'her', $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_HIS_HER', '<span style="color:red">SYS_CONTACT_PV_CLIENT_HIS_HER</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_LASTNAME':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_LASTNAME', $psmf->getContactPvClient()->getLastName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_LASTNAME', '<span style="color:red">SYS_CONTACT_PV_CLIENT_LASTNAME</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_MAIL':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_MAIL', $psmf->getContactPvClient()->getEmail(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_MAIL', '<span style="color:red">SYS_CONTACT_PV_CLIENT_MAIL</span>', $contenu);
                    }
                    break;
                case 'SYS_CONTACT_PV_CLIENT_MOBILE':
                    if ($psmf) {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_MOBILE', $psmf->getContactPvClient()->getMobile(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_CONTACT_PV_CLIENT_MOBILE', '<span style="color:red">SYS_CONTACT_PV_CLIENT_MOBILE</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_ADRESS':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_ADRESS', $psmf->getDeputyEUQPPV()->getAdresse(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_ADRESS', '<span style="color:red">SYS_DEPUTY_EUQPPV_ADRESS</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_COMPANY':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_COMPANY', $psmf->getDeputyEUQPPV()->getWorkName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_COMPANY', '<span style="color:red">SYS_DEPUTY_EUQPPV_COMPANY</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_CV':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        if ($psmf->getDeputyEUQPPV()->getCvUri()) {
                            if ($format == PSMF::PDF) {
                                if (is_file($this->kernel->getProjectDir() . '/data/' . $psmf->getDeputyEUQPPV()->getCvUri())) {
                                    $contenu = str_replace('SYS_DEPUTY_EUQPPV_CV', '<img src="' . $this->kernel->getProjectDir() . '/data/' . $psmf->getDeputyEUQPPV()->getCvUri() . '"/>', $contenu);
                                } else {
                                    $contenu = str_replace('SYS_DEPUTY_EUQPPV_CV', '<img src="' . $this->kernel->getProjectDir() . '/data/' .  PSMF::NOT_FOUND_IMAGE . '"/>', $contenu);
                                }
                            } else {
                                if ($format == PSMF::WORD) {
                                    $contenu = str_replace('SYS_DEPUTY_EUQPPV_CV', '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $psmf->getDeputyEUQPPV()->getCvUri() . '"/>', $contenu);
                                } else {
                                    $contenu = str_replace('SYS_DEPUTY_EUQPPV_CV', '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $psmf->getDeputyEUQPPV()->getCvUri() . '"/>', $contenu);
                                }
                            }
                        } else {
                            $contenu = str_replace('SYS_DEPUTY_EUQPPV_CV', '<span style="color:red">SYS_DEPUTY_EUQPPV_CV</span>', $contenu);
                        }
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_CV', '<span style="color:red">SYS_DEPUTY_EUQPPV_CV</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_FAX':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_FAX', $psmf->getDeputyEUQPPV()->getFax(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_FAX', '<span style="color:red">SYS_DEPUTY_EUQPPV_FAX</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_FIRSTNAME':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_FIRSTNAME', $psmf->getDeputyEUQPPV()->getFirstName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_FIRSTNAME', '<span style="color:red">SYS_DEPUTY_EUQPPV_FIRSTNAME</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_FIX':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_FIX', $psmf->getDeputyEUQPPV()->getFixe(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_FIX', '<span style="color:red">SYS_DEPUTY_EUQPPV_FIX</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_FUNCTION':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_FUNCTION', $psmf->getDeputyEUQPPV()->getWorkFunction(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_FUNCTION', '<span style="color:red">SYS_DEPUTY_EUQPPV_FUNCTION</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_HE_SHE':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_HE_SHE', ($psmf->getDeputyEUQPPV()->getGender()->getCode() == Gender::HOMME) ? 'he' : 'she', $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_HE_SHE', '<span style="color:red">SYS_DEPUTY_EUQPPV_HE_SHE</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_HIM_HER':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_HIM_HER', ($psmf->getDeputyEUQPPV()->getGender()->getCode() == Gender::HOMME) ? 'him' : 'her', $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_HIM_HER', '<span style="color:red">SYS_DEPUTY_EUQPPV_HIM_HER</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_HIS_HER':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_HIS_HER', ($psmf->getDeputyEUQPPV()->getGender()->getCode() == Gender::HOMME) ? 'his' : 'her', $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_HIS_HER', '<span style="color:red">SYS_DEPUTY_EUQPPV_HIS_HER</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_LASTNAME':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_LASTNAME', $psmf->getDeputyEUQPPV()->getLastName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_LASTNAME', '<span style="color:red">SYS_DEPUTY_EUQPPV_LASTNAME</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_MAIL':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_MAIL', $psmf->getDeputyEUQPPV()->getEmail(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_MAIL', '<span style="color:red">SYS_DEPUTY_EUQPPV_MAIL</span>', $contenu);
                    }
                    break;
                case 'SYS_DEPUTY_EUQPPV_MOBILE':
                    if ($psmf && $psmf->getDeputyEUQPPV()) {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_MOBILE', $psmf->getDeputyEUQPPV()->getMobile(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_DEPUTY_EUQPPV_MOBILE', '<span style="color:red">SYS_DEPUTY_EUQPPV_MOBILE</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_ADRESS':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_ADRESS', $psmf->getEuQPPV()->getAdresse(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_ADRESS', '<span style="color:red">SYS_EUQPPV_ADRESS</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_COMPANY':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_COMPANY', $psmf->getEuQPPV()->getWorkName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_COMPANY', '<span style="color:red">SYS_EUQPPV_COMPANY</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_CV':
                    if ($psmf) {
                        if ($psmf->getEuQPPV()->getCvUri()) {
                            if ($format == PSMF::PDF) {
                                if (is_file($this->kernel->getProjectDir() . '/data/' . $psmf->getEuQPPV()->getCvUri())) {
                                    $contenu = str_replace('SYS_EUQPPV_CV', '<img src="' . $this->kernel->getProjectDir() . '/data/' . $psmf->getEuQPPV()->getCvUri() . '"/>', $contenu);
                                } else {
                                    $contenu = str_replace('SYS_EUQPPV_CV', '<img src="' . $this->kernel->getProjectDir() . '/data/' . PSMF::NOT_FOUND_IMAGE . '"/>', $contenu);
                                }
                            } else {
                                if ($format == PSMF::WORD) {
                                    $contenu = str_replace('SYS_EUQPPV_CV', '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $psmf->getEuQPPV()->getCvUri() . '"/>', $contenu);
                                } else {
                                    $contenu = str_replace('SYS_EUQPPV_CV', '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $psmf->getEuQPPV()->getCvUri() . '"/>', $contenu);
                                }
                            }
                        } else {
                            $contenu = str_replace('SYS_EUQPPV_CV', '', $contenu);
                        }
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_CV', '<span style="color:red">SYS_EUQPPV_CV</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_FAX':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_FAX', $psmf->getEuQPPV()->getFax(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_FAX', '<span style="color:red">SYS_EUQPPV_FAX</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_FIRSTNAME':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_FIRSTNAME', $psmf->getEuQPPV()->getFirstName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_FIRSTNAME', '<span style="color:red">SYS_EUQPPV_FIRSTNAME</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_FIX':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_FIX', $psmf->getEuQPPV()->getFixe(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_FIX', '<span style="color:red">SYS_EUQPPV_FIX</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_RESP_FUNCTION':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_RESP_FUNCTION', $psmf->getEuQPPV()->getWorkFunction(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_RESP_FUNCTION', '<span style="color:red">SYS_EUQPPV_RESP_FUNCTION</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_HE_SHE':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_HE_SHE', ($psmf->getEuQPPV()->getGender()->getCode() == Gender::HOMME) ? 'he' : 'she', $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_HE_SHE', '<span style="color:red">SYS_EUQPPV_HE_SHE</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_HIM_HER':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_HIM_HER', ($psmf->getEuQPPV()->getGender()->getCode() == Gender::HOMME) ? 'him' : 'her', $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_HIM_HER', '<span style="color:red">SYS_EUQPPV_HIM_HER</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_HIS_HER':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_HIS_HER', ($psmf->getEuQPPV()->getGender()->getCode() == Gender::HOMME) ? 'his' : 'her', $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_HIS_HER', '<span style="color:red">SYS_EUQPPV_HIS_HER</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_MAJ_HIS_HER':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_MAJ_HIS_HER', ($psmf->getEuQPPV()->getGender()->getCode() == Gender::HOMME) ? 'His' : 'Her', $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_MAJ_HIS_HER', '<span style="color:red">SYS_EUQPPV_MAJ_HIS_HER</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_LASTNAME':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_LASTNAME', $psmf->getEuQPPV()->getLastName(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_LASTNAME', '<span style="color:red">SYS_EUQPPV_LASTNAME</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_MAIL':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_MAIL', $psmf->getEuQPPV()->getEmail(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_MAIL', '<span style="color:red">SYS_EUQPPV_MAIL</span>', $contenu);
                    }
                    break;
                case 'SYS_EUQPPV_MOBILE':
                    if ($psmf) {
                        $contenu = str_replace('SYS_EUQPPV_MOBILE', $psmf->getEuQPPV()->getMobile(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_EUQPPV_MOBILE', '<span style="color:red">SYS_EUQPPV_MOBILE</span>', $contenu);
                    }
                    break;
                case 'SYS_PSMF_EUDRA_NUMBER':
                    if ($psmf) {
                        $contenu = str_replace('SYS_PSMF_EUDRA_NUMBER', $psmf->getEudravigNum(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_PSMF_EUDRA_NUMBER', '<span style="color:red">SYS_PSMF_EUDRA_NUMBER</span>', $contenu);
                    }
                    break;
                case 'SYS_PSMF_LAST_MAJ':
                    if ($psmf) {
                        $contenu = str_replace('SYS_PSMF_LAST_MAJ', $psmf->getUpdateDate()->format('d-M-Y'), $contenu);
                    } else {
                        $contenu = str_replace('SYS_PSMF_LAST_MAJ', '<span style="color:red">SYS_PSMF_LAST_MAJ</span>', $contenu);
                    }
                    break;
                case 'SYS_PSMF_LAST_USER':
                    if ($psmf) {
                        $contenu = str_replace('SYS_PSMF_LAST_USER', $psmf->getUpdateUser(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_PSMF_LAST_USER', '<span style="color:red">SYS_PSMF_LAST_USER</span>', $contenu);
                    }
                    break;
                case 'SYS_PSMF_LAST_VERSION':
                    if ($psmf) {
                        if ($psmf->hasLastVersion()) {
                            $contenu = str_replace('SYS_PSMF_LAST_VERSION', $psmf->getLastVersion()->getVersion() + 1, $contenu);
                        } else {
                            $contenu = str_replace('SYS_PSMF_LAST_VERSION', '1', $contenu);
                        }
                    } else {
                        $contenu = str_replace('SYS_PSMF_LAST_VERSION', '<span style="color:red">SYS_PSMF_LAST_VERSION</span>', $contenu);
                    }
                    break;
                case 'SYS_PSMF_TITRE':
                    if ($psmf) {
                        $contenu = str_replace('SYS_PSMF_TITRE', $psmf->getTitle(), $contenu);
                    } else {
                        $contenu = str_replace('SYS_PSMF_TITRE', '<span style="color:red">SYS_PSMF_TITRE</span>', $contenu);
                    }
                    break;
                case 'SYS_TODAY':
                    if ($psmf) {
                        $contenu = str_replace('SYS_TODAY', (new \DateTime())->format('d-M-Y'), $contenu);
                    } else {
                        $contenu = str_replace('SYS_TODAY', '<span style="color:red">SYS_TODAY</span>', $contenu);
                    }
                    break;
                case 'HISTORIQUE_VESRION_MAIN_BODY_MOINS_5ANS':
                    if ($psmf) {
                        if ($psmf->getLastVersion()) {
                            $version = $psmf->getLastVersion()->getVersion() + 1;
                        } else {
                            $version = "1";
                        }
                        
                        $pSMFVariableHistories = $this->pSMFHistoryRepository->findByHistoryVersionMainBodyMoins5Ans($psmf);
                        $variablesHistory = $this->twig->render(
                            'PSMFManagement/PSMFHistory/variables-export.html.twig',
                            [
                                'pSMFVariableHistories' => $pSMFVariableHistories,
                                'version' => $version
                            ]
                        );
                        $pSMFSectionHistories = $this->pSMFHistoryRepository->findByHistoryVersionMainBodyMoins5AnsSection($psmf);
                        $sectionsHistory = $this->twig->render(
                            'PSMFManagement/PSMFHistory/sections-export.html.twig',
                            [
                                'pSMFSectionHistories' => $pSMFSectionHistories,
                                'version' => $version
                            ]
                        );
                        $contenu = str_replace('HISTORIQUE_VESRION_MAIN_BODY_MOINS_5ANS', $variablesHistory . '<br/>' . $sectionsHistory, $contenu);
                    } else {
                        $contenu = str_replace('HISTORIQUE_VESRION_MAIN_BODY_MOINS_5ANS', '<span style="color:red">HISTORIQUE_VESRION_MAIN_BODY_MOINS_5ANS</span>', $contenu);
                    }
                    break;
                case 'HISTORIQUE_VESRION_PSMF_ANNEXES_MOINS_5ANS':
                    if ($psmf) {
                        if ($psmf->getLastVersion()) {
                            $version = $psmf->getLastVersion()->getVersion() + 1;
                        } else {
                            $version = "1";
                        }

                        $pSMFVariableHistories = $this->pSMFHistoryRepository->findByHistoryVersionPSMFAnnexesMoins5Ans($psmf);
                        $variablesHistory = $this->twig->render(
                            'PSMFManagement/PSMFHistory/variables-export.html.twig',
                            [
                                'pSMFVariableHistories' => $pSMFVariableHistories,
                                'version' => $version
                            ]
                        );
                        $pSMFSectionHistories = $this->pSMFHistoryRepository->findByHistoryVersionPSMFAnnexesMoins5AnsSection($psmf);
                        $sectionsHistory = $this->twig->render(
                            'PSMFManagement/PSMFHistory/sections-export.html.twig',
                            [
                                'pSMFSectionHistories' => $pSMFSectionHistories,
                                'version' => $version
                            ]
                        );
                        $contenu = str_replace('HISTORIQUE_VESRION_PSMF_ANNEXES_MOINS_5ANS', $variablesHistory . '<br/>' . $sectionsHistory, $contenu);
                    } else {
                        $contenu = str_replace('HISTORIQUE_VESRION_PSMF_ANNEXES_MOINS_5ANS', '<span style="color:red">HISTORIQUE_VESRION_PSMF_ANNEXES_MOINS_5ANS</span>', $contenu);
                    }
                    break;
            }
        }
        return $contenu;
    }

    /*
    * le parser globale de globales
    */
    public function globalesParser(string $contenu, string $format, bool $parser = true, bool $monter_de_version = false): string
    {
        $globales = $this->variableRepository->findEquivalencesGlobales();
        $request = $this->requestStack->getCurrentRequest();
        foreach ($globales as $globale) {
            if ($parser) {
                // (text, integer, Option, Autre, Date),  (Image), (TextLong)
                switch ($globale->getType()->getCode()) {
                    case TypeVariable::IMAGE:
                        if ($format == PSMF::PDF) {
                            if (is_file($this->kernel->getProjectDir() . '/data/' . $globale->getCorrespondanceGlobale()->getValueLocal())) {
                                $contenu = str_replace($globale->getBalise(), '<img src="' . $this->kernel->getProjectDir() . '/data/' . $globale->getCorrespondanceGlobale()->getValueLocal() . '"/>', $contenu);
                            } else {
                                $contenu = str_replace($globale->getBalise(), '<img src="' . $this->kernel->getProjectDir() . '/data/' . PSMF::NOT_FOUND_IMAGE . '"/>', $contenu);
                            }
                        } else {
                            if ($format == PSMF::WORD) {
                                $contenu = str_replace($globale->getBalise(), '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $globale->getCorrespondanceGlobale()->getValueLocal() . '"/>', $contenu);
                            } else {
                                $contenu = str_replace($globale->getBalise(), '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $globale->getCorrespondanceGlobale()->getValueLocal() . '"/>', $contenu);
                            }
                        }
                        break;
                    default:
                        if (!empty($globale->getCorrespondanceGlobale()) && !empty($globale->getCorrespondanceGlobale()->getValueLocal())) {
                            $contenu = str_replace($globale->getBalise(), $globale->getCorrespondanceGlobale()->getValueLocal(), $contenu);
                        } else {
                            if ($globale->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                                $contenu = str_replace($globale->getBalise(), '<span style="color:red">' . $globale->getBalise() . '</span>', $contenu);
                            } else {
                                if (!$monter_de_version) {
                                    $contenu = str_replace($globale->getBalise(), '<span style="color:green">' . $globale->getBalise() . '</span>', $contenu);
                                } else {
                                    $contenu = str_replace($globale->getBalise(), '', $contenu);
                                }
                            }
                        }
                        break;
                }
            } else {
                if ($globale->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                    $contenu = str_replace($globale->getBalise(), '<span style="color:red">' . $globale->getBalise() . '</span>', $contenu);
                } else {
                    $contenu = str_replace($globale->getBalise(), '<span style="color:green">' . $globale->getBalise() . '</span>', $contenu);
                }
            }
        }
        return $contenu;
    }

    /*
    * le parser globale de locale
    */
    public function localesParser(?PSMF $psmf, string $contenu, string $format, bool $monter_de_version = false): string
    {
        $locales = $this->variableRepository->findEquivalencesLocales();
        $request = $this->requestStack->getCurrentRequest();
        foreach ($locales as $locale) {
            if ($psmf) {
                // (text, integer, Option, Autre, Date),  (Image), (TextLong)
                $correspondance = $this->correspondanceRepository->findOneBy(['psmf' => $psmf, 'variable' => $locale]);
                if ($correspondance) { // if value est remplit
                    switch ($locale->getType()->getCode()) {
                        case TypeVariable::IMAGE:
                            if ($format == PSMF::PDF) { // en format pdf
                                if (!empty($correspondance->getValueLocal()) && is_file($this->kernel->getProjectDir() . '/data/' . $correspondance->getValueLocal())) {
                                    $contenu = str_replace($locale->getBalise(), '<img src="' . $this->kernel->getProjectDir() . '/data/' . $correspondance->getValueLocal() . '"/>', $contenu);
                                } else {
                                    // en rouge si elles sont obligatoires en vert sinon). 
                                    if ($locale->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                                        $contenu = str_replace($locale->getBalise(), '<span style="color:red">' . $locale->getBalise() . '</span>', $contenu);
                                    } else {
                                        if (!$monter_de_version) {
                                            $contenu = str_replace($locale->getBalise(), '<span style="color:green">' . $locale->getBalise() . '</span>', $contenu);
                                        } else {
                                            $contenu = str_replace($locale->getBalise(), '', $contenu);
                                        }
                                    }
                                    //$contenu = str_replace($locale->getBalise(), '', $contenu);
                                }
                            } else {
                                if ($format == PSMF::WORD) { // en format word
                                    if (!empty($correspondance->getValueLocal()) && is_file($this->kernel->getProjectDir() . '/data/' . $correspondance->getValueLocal())) {
                                        $contenu = str_replace($locale->getBalise(), '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $correspondance->getValueLocal() . '"/>', $contenu);
                                    } else {
                                        if ($locale->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                                            $contenu = str_replace($locale->getBalise(), '<span style="color:red">' . $locale->getBalise() . '</span>', $contenu);
                                        } else {
                                            if (!$monter_de_version) {
                                                $contenu = str_replace($locale->getBalise(), '<span style="color:green">' . $locale->getBalise() . '</span>', $contenu);
                                            } else {
                                                $contenu = str_replace($locale->getBalise(), '', $contenu);
                                            }
                                        }
                                        //$contenu = str_replace($locale->getBalise(), '', $contenu);
                                    }
                                } else { // en format html
                                    if (!empty($correspondance->getValueLocal()) && is_file($this->kernel->getProjectDir() . '/data/' . $correspondance->getValueLocal())) {
                                        $contenu = str_replace($locale->getBalise(), '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $correspondance->getValueLocal() . '"/>', $contenu);
                                    } else {
                                        if ($locale->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                                            $contenu = str_replace($locale->getBalise(), '<span style="color:red">' . $locale->getBalise() . '</span>', $contenu);
                                        } else {
                                            if (!$monter_de_version) {
                                                $contenu = str_replace($locale->getBalise(), '<span style="color:green">' . $locale->getBalise() . '</span>', $contenu);
                                            } else {
                                                $contenu = str_replace($locale->getBalise(), '', $contenu);
                                            }
                                        }
                                    }
                                }
                            }
                            break;
                        case TypeVariable::OPTION:
                            if ($correspondance->getValueLocal() == "Client") {
                                if (!$monter_de_version) {
                                    $contenu = $this->str_replace_first($locale->getBalise(), '<span style="background:#FFFF00">X</span>', $contenu);
                                } else {
                                    $contenu = $this->str_replace_first($locale->getBalise(), 'X', $contenu);
                                }
                                $contenu = $this->str_replace_first($locale->getBalise(), '', $contenu);
                            } elseif ($correspondance->getValueLocal() == "Les deux") {
                                if (!$monter_de_version) {
                                    $contenu = $this->str_replace_first($locale->getBalise(), '<span style="background:#FFFF00">X</span>', $contenu);
                                    $contenu = $this->str_replace_first($locale->getBalise(), '<span style="background:#FFFF00">X</span>', $contenu);
                                } else {
                                    $contenu = $this->str_replace_first($locale->getBalise(), 'X', $contenu);
                                    $contenu = $this->str_replace_first($locale->getBalise(), 'X', $contenu);
                                }
                            } else {
                                $contenu = $this->str_replace_first($locale->getBalise(), '', $contenu);
                                if (!$monter_de_version) {
                                    $contenu = $this->str_replace_first($locale->getBalise(), '<span style="background:#FFFF00">X</span>', $contenu);
                                } else {
                                    $contenu = $this->str_replace_first($locale->getBalise(), 'X', $contenu);
                                }
                            }
                            break;
                        default:
                            if (!empty($correspondance->getValueLocal())) {
                                if (!$monter_de_version) {
                                    // Si les balises contiennent des valeurs locales alors les surligner en jaune
                                    if ($locale->getType()->getCode() == TypeVariable::TEXT_LONG) {
                                        $contenu = str_replace($locale->getBalise(), '<table style="background-color:#FFFF00;" border="0" cellspacing="0" cellpadding="0" ><tr><td>'.$correspondance->getValueLocal(). "</td></tr></table>", $contenu);
                                    } else {
                                        $contenu = str_replace($locale->getBalise(), '<span style="background-color:#FFFF00">' . $correspondance->getValueLocal() . '</span>', $contenu);
                                    }
                                    
                                } else {
                                    $contenu = str_replace($locale->getBalise(), $correspondance->getValueLocal(), $contenu);
                                }
                            } else {
                                if ($locale->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                                    $contenu = str_replace($locale->getBalise(), '<span style="color:red">' . $locale->getBalise() . '</span>', $contenu);
                                } else {
                                    if (!$monter_de_version) {
                                        $contenu = str_replace($locale->getBalise(), '<span style="color:green">' . $locale->getBalise() . '</span>', $contenu);
                                    } else {
                                        $contenu = str_replace($locale->getBalise(), '', $contenu);
                                    }
                                }
                            }
                            break;
                    }
                } else { // if value est vide
                    switch ($locale->getType()->getCode()) {
                        case TypeVariable::OPTION:
                            $contenu = str_replace('{% if ' . $locale->getBalise() . ' == "Client" or ' . $locale->getBalise() . ' == "Les deux" %}X{% endif %}', '<span style="color:red">?</span>', $contenu);
                            $contenu = str_replace('{% if ' . $locale->getBalise() . ' == "UM" or ' . $locale->getBalise() . ' == "Les deux" %}X{% endif %}', '<span style="color:red">?</span>', $contenu);
                            break;
                        default:
                            if ($locale->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                                $contenu = str_replace($locale->getBalise(), '<span style="color:red">' . $locale->getBalise() . '</span>', $contenu);
                            } else {
                                $contenu = str_replace($locale->getBalise(), '<span style="color:green">' . $locale->getBalise() . '</span>', $contenu);
                            }
                            break;
                    }
                }
            } else { // if template preview
                switch ($locale->getType()->getCode()) {
                    default:
                        if ($locale->getObligation()->getCode() == Obligation::OBLIGATOIRE) {
                            $contenu = str_replace($locale->getBalise(), '<span style="color:red">' . $locale->getBalise() . '</span>', $contenu);
                        } else {
                            $contenu = str_replace($locale->getBalise(), '<span style="color:green">' . $locale->getBalise() . '</span>', $contenu);
                        }
                        break;
                }
            }
        }
        return $contenu;
    }

    /*
    * Qualios Parser
    */
    public function qualiosParser(string $contenu, bool $parser = true, bool $monter_de_version = false): string
    {
        $qualios = $this->documentRepository->findAll();
        foreach ($qualios as $qualio) {
            $contenu = $this->qualioBaliseReplace($qualio, $contenu, $parser, $monter_de_version);
        }
        return $contenu;
    }

    /**
     * Qualios balise parsers one by one
     */
    private function qualioBaliseReplace(Document $qualio, string $contenu, bool $parser = true, bool $monter_de_version = false): ?string
    {
        if ($parser) {
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_VERSION', $qualio->getVersion(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_TITLE', $qualio->getTitle(), $contenu);

            if ($qualio->getActivityDate()) {
                $activityDate = $qualio->getActivityDate()->format('d-M-Y');
            } else {
                if ($monter_de_version) {
                    $activityDate = '';
                } else {
                    $activityDate = '<span style="color:red">QUALIOS_' . $qualio->getReference() . '_ACTIVITYDATE</span>';
                }
            }
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ACTIVITYDATE', $activityDate, $contenu);

            if ($qualio->getCreateDate()) {
                $createDate = $qualio->getCreateDate()->format('d-M-Y');
            } else {
                if ($monter_de_version) {
                    $createDate = '';
                } else {
                    $createDate = '<span style="color:red">QUALIOS_' . $qualio->getReference() . '_CREATEDATE</span>';
                }
            }
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_CREATEDATE', $createDate, $contenu);

            if ($qualio->getValidityDate()) {
                $validityDate = $qualio->getValidityDate()->format('d-M-Y');
            } else {
                if ($monter_de_version) {
                    $validityDate = '';
                } else {
                    $validityDate = '<span style="color:red">QUALIOS_' . $qualio->getReference() . '_VALIDITYDATE</span>';
                }
            }
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_VALIDITYDATE', $validityDate, $contenu);

            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_NATUREOFCHANGE', $qualio->getNatureOfChange(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_OBJECT', $qualio->getObject(), $contenu);

            if ($qualio->getArchiveDate()) {
                $archiveDate = $qualio->getArchiveDate()->format('d-M-Y');
            } else {
                if ($monter_de_version) {
                    $archiveDate = '';
                } else {
                    $archiveDate = '<span style="color:red">QUALIOS_' . $qualio->getReference() . '_ARCHIVEDATE</span>';
                }
            }
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ARCHIVEDATE', $archiveDate, $contenu);

            if ($qualio->getReviewDate()) {
                $reviewDate = $qualio->getReviewDate()->format('d-M-Y');
            } else {
                if ($monter_de_version) {
                    $reviewDate = '';
                } else {
                    $reviewDate = '<span style="color:red">QUALIOS_' . $qualio->getReference() . '_REVIEWDATE</span>';
                }
            }
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_REVIEWDATE', $reviewDate, $contenu);

            if ($qualio->getSubmitDate()) {
                $submitDate = $qualio->getSubmitDate()->format('d-M-Y');
            } else {
                if ($monter_de_version) {
                    $submitDate = '';
                } else {
                    $submitDate = '<span style="color:red">QUALIOS_' . $qualio->getReference() . '_SUBMITDATE</span>';
                }
            }
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_SUBMITDATE', $submitDate, $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_SIGNDELAY', $qualio->getSignDelay(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_LATESIGNALERT', $qualio->isLateSignAlert() ? 'Yes' : 'No', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_FORMATIONACCESSIBILITY', $qualio->isFormationAccessibility() ? 'Yes' : 'No', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ACTIONWHENARCHIVE', $qualio->getActionWhenArchive(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ARCHIVEPERIOD', $qualio->getArchivePeriod(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ARCHIVEPERIODALERT', $qualio->isArchivePeriodAlert() ? 'Yes' : 'No', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_REVIEWALERT', $qualio->isReviewAlert() ? 'Yes' : 'No', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_DOCREFQUALIFORM', $qualio->getDocRefQualifForm(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_RAPPELQUALIFORM', $qualio->isRappelQualifForm() ? 'Yes' : 'No', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_SIGNBYLEVEL', $qualio->getSignByLevel(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_NBLEVEL', $qualio->getNbLevel(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_DOCCODE', $qualio->getDocCode(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_TITLECOMPOSITION', $qualio->getTitleComposition(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_TESTMODE', $qualio->getTestMode(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_DELAYVALIDITY', $qualio->isDelayValidity() ? 'Yes' : 'No', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_DELAYAR', $qualio->isDelayAR() ? 'Yes' : 'No', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ATTACHMENTASBODY', $qualio->isAttachmentAsBody() ? 'Yes' : 'No', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_TYPE', $qualio->getType(), $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_STATUS', $qualio->getStatus(), $contenu);
        } else {
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_VERSION', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_VERSION</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_TITLE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_TITLE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ACTIVITYDATE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_ACTIVITYDATE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_CREATEDATE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_CREATEDATE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_VALIDITYDATE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_VALIDITYDATE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_NATUREOFCHANGE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_NATUREOFCHANGE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_OBJECT', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_OBJECT</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ARCHIVEDATE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_ARCHIVEDATE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_REVIEWDATE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_REVIEWDATE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_SUBMITDATE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_SUBMITDATE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_SIGNDELAY', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_SIGNDELAY</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_LATESIGNALERT', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_LATESIGNALERT</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_FORMATIONACCESSIBILITY', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_FORMATIONACCESSIBILITY</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ACTIONWHENARCHIVE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_ACTIONWHENARCHIVE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ARCHIVEPERIOD', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_ARCHIVEPERIOD</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ARCHIVEPERIODALERT', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_ARCHIVEPERIODALERT</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_REVIEWALERT', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_REVIEWALERT</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_DOCREFQUALIFORM', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_DOCREFQUALIFORM</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_RAPPELQUALIFORM', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_RAPPELQUALIFORM</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_SIGNBYLEVEL', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_SIGNBYLEVEL</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_NBLEVEL', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_NBLEVEL</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_DOCCODE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_DOCCODE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_TITLECOMPOSITION', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_TITLECOMPOSITION</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_TESTMODE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_TESTMODE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_DELAYVALIDITY', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_DELAYVALIDITY</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_DELAYAR', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_DELAYAR</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_ATTACHMENTASBODY', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_ATTACHMENTASBODY</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_TYPE', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_TYPE</span>', $contenu);
            $contenu = str_replace('QUALIOS_' . $qualio->getReference() . '_STATUS', '<span style="color:green">QUALIOS_' . $qualio->getReference() . '_STATUS</span>', $contenu);
        }
        return $contenu;
    }
    /*
    * generate a psmf document
    */
    public function pSMFGenerator(PSMF $psmf, string $format): string
    {
        $uri =
            $header = $this->sectionRepository->find(Section::HEADER_ID);
        $footer = $this->sectionRepository->find(Section::FOOTER_ID);
        $sections = $this->sectionRepository->findBy([
            'template' => Template::TEMPLATE_ID,
            'parent' => NULL,
            'isValid' => true,
            'isDeleted' => false
        ], [
            'position' => 'ASC',
        ]);

        $contenu = "";
        $pages = [];
        $i = 0;
        switch ($format) {
            case PSMF::WORD:
                foreach ($sections as $section) {
                    if ($section->getId() > 2) {
                        $contenu .= $this->sectionParser($section, $section->getContenu());
                        foreach ($section->getSections() as $subSection) {
                            if ($subSection->getIsValid()) {
                                $contenu .= "<br>";
                                $contenu .= $this->sectionParser($subSection, $subSection->getContenu());
                                foreach ($subSection->getSections() as $_subSection) {
                                    if ($_subSection->getIsValid()) {
                                        $contenu .= "<br>";
                                        $contenu .= $this->sectionParser($_subSection, $_subSection->getContenu());
                                    }
                                }
                            }
                        }
                    }
                }
                $contenu = $this->tableContentsParser($sections, $contenu);
                $contenu = $this->localesParser($psmf, $contenu, $format, true);
                $contenu = $this->systemesParser($psmf, $contenu, $format);
                $contenu = $this->globalesParser($contenu, $format, true);
                $contenu = $this->qualiosParser($contenu, true);
                $contenu = $this->logiqueParser($psmf, $contenu, true);
                
                $template = $this->twig->createTemplate($contenu);
                $contenu = $template->render(['psmf' => $psmf]);

                try {
                    $htd = new HtmlToDoc();
                    $htd->setTitle($psmf->getTitle());
                    //$htd->setHeader($header->getContenu());
                    $docfileName = $this->slugger->slug($psmf->getTitle() . '_' . (new \DateTime())->format('d-M-Y H:i:s')) . '.doc';
                    $docfilePath = $this->kernel->getProjectDir() . '/PSMD/' . $docfileName;
                    $htd->setDocFilePath($docfilePath);
                    $htd->setDocFileName($docfileName);
                    $htd->createDoc($contenu, $docfileName, false);
                    return $docfilePath;
                } catch (\Throwable $e) {
                    throw new \ErrorException($e->getMessage(), $e->getCode(), \E_ERROR, $e->getFile(), $e->getLine());
                }
                break;
            case PSMF::PDF:
                foreach ($sections as $section) {
                    if ($section->getId() > 2) {
                        $pages[$i] = $this->sectionParser($section, $section->getContenu());
                        foreach ($section->getSections() as $subSection) {
                            if ($subSection->getIsValid()) {
                                //$pages[$i] .= "<br>";
                                if ($subSection->getIsPageBreak()) {
                                    $pages[$i] .= "<pagebreak>";
                                }
                                $pages[$i] .= $this->sectionParser($subSection, $subSection->getContenu());
                                foreach ($subSection->getSections() as $_subSection) {
                                    if ($_subSection->getIsValid()) {
                                        //$pages[$i] .= "<br/>";
                                        if ($_subSection->getIsPageBreak()) {
                                            $pages[$i] .= "<pagebreak>";
                                        }
                                        $pages[$i] .= $this->sectionParser($_subSection, $_subSection->getContenu());
                                    }
                                }
                            }
                        }
                        $pages[$i] = $this->tableContentsParser($sections, $pages[$i]);
                        $pages[$i] = $this->localesParser($psmf, $pages[$i], $format, true);
                        $pages[$i] = $this->systemesParser($psmf, $pages[$i], $format);
                        $pages[$i] = $this->globalesParser($pages[$i], $format, true);
                        $pages[$i] = $this->qualiosParser($pages[$i], true);
                        $pages[$i] = $this->logiqueParser($psmf, $pages[$i]);

                        $template = $this->twig->createTemplate($pages[$i]);
                        $pages[$i] = $template->render(['psmf' => $psmf]);
                        $i++;
                    }
                }

                $head = $this->systemesParser($psmf, $header->getContenu(), $format);
                $head = $this->globalesParser($head, $format);
                $head = $this->qualiosParser($head);
                $head = $this->localesParser($psmf, $head, $format, true);

                $foot = $this->systemesParser($psmf, $footer->getContenu(), $format);
                $foot = $this->globalesParser($foot, $format);
                $foot = $this->qualiosParser($foot);
                $foot = $this->localesParser($psmf, $foot, $format, true);

                $contenu = $this->twig->render(
                    'PSMFManagement/PSMF/draft/pdf2.html.twig',
                    [
                        'header' => $head,
                        'footer' => $foot,
                        'pages' => $pages
                    ]
                );

                $request = $this->requestStack->getCurrentRequest();
                if ($request->get('debug')) return new Response($contenu);

                try {
                    $contenu = $this->converter->convertToPdf($contenu);

                    $docfile = $this->kernel->getProjectDir() . '/PSMD/' . $this->slugger->slug($psmf->getTitle() . '_' . (new \DateTime())->format('d-M-Y H:i:s')) . '.pdf';
                    $filesystem = new Filesystem();
                    $filesystem->dumpFile($docfile, $contenu);
                    return $docfile;
                } catch (\Throwable $e) {
                    throw new \ErrorException($e->getMessage(), $e->getCode(), \E_ERROR, $e->getFile(), $e->getLine());
                }
                break;
            case PSMF::HTML:
            default:
                foreach ($sections as $section) {
                    if ($section->getId() > 2) {
                        $pages[$i] = $this->sectionParser($section, $section->getContenu());
                        foreach ($section->getSections() as $subSection) {
                            $pages[$i] .= "<br>";
                            $pages[$i] .= $this->sectionParser($subSection, $subSection->getContenu());
                            foreach ($subSection->getSections() as $_subSection) {
                                if ($_subSection->getIsValid()) {
                                    $pages[$i] .= "<br/>";
                                    $pages[$i] .= $this->sectionParser($_subSection, $_subSection->getContenu());
                                }
                            }
                        }
                        $pages[$i] = $this->tableContentsParser($sections, $pages[$i]);
                        $pages[$i] = $this->localesParser($psmf, $pages[$i], $format, true);
                        $pages[$i] = $this->systemesParser($psmf, $pages[$i], $format);
                        $pages[$i] = $this->globalesParser($pages[$i], $format, true);
                        $pages[$i] = $this->qualiosParser($pages[$i], true);
                        $pages[$i] = $this->logiqueParser($psmf, $pages[$i]);

                        $template = $this->twig->createTemplate($pages[$i]);
                        $pages[$i] = $template->render(['psmf' => $psmf]);
                        $i++;
                    }
                }
                $head = $this->systemesParser($psmf, $header->getContenu(), $format);
                $head = $this->globalesParser($head, $format);
                $head = $this->qualiosParser($head);
                $head = $this->localesParser($psmf, $head, $format, true);

                $foot = $this->systemesParser($psmf, $footer->getContenu(), $format);
                $foot = $this->globalesParser($foot, $format);
                $foot = $this->qualiosParser($foot);
                $foot = $this->localesParser($psmf, $foot, $format, true);

                $contenu = $this->twig->render('PSMFManagement/PSMF/draft/html.html.twig', [
                    'header' => $head,
                    'footer' => $foot,
                    'pages' => $pages
                ]);
                $docfile = $this->kernel->getProjectDir() . '/PSMD/' . $this->slugger->slug($psmf->getTitle() . '_' . (new \DateTime())->format('d-M-Y H:i:s')) . '.html';
                $filesystem = new Filesystem();
                $filesystem->dumpFile($docfile, $contenu);
                return $docfile;
                break;
        }
    }

    /*
    * getSectionDetails
    */
    public function getSectionDetails(): array
    {
        $globales = $this->variableRepository->findEquivalencesGlobales();
        $locales = $this->variableRepository->findEquivalencesLocales();
        $request = $this->requestStack->getCurrentRequest();
        $details = [];
        foreach ($globales as $globale) {
            $details[] = $globale->getBalise();
        }

        foreach ($locales as $locale) {
            $details[] = $locale->getBalise();
        }

        ksort($details);

        return $details;
    }

    /*
    * get globals and locals VariableDetails by psmf
    */
    public function getVariableDetails(PSMF $psmf, string $format): array
    {
        $globales = $this->variableRepository->findEquivalencesGlobales();
        $locales = $this->variableRepository->findEquivalencesLocales();
        $request = $this->requestStack->getCurrentRequest();
        $details = [];

        foreach ($globales as $globale) {
            switch ($globale->getType()->getCode()) {
                case TypeVariable::IMAGE:
                    if ($format == PSMF::PDF) {
                        if (is_file($this->kernel->getProjectDir() . '/data/' . $globale->getCorrespondanceGlobale()->getValueLocal())) {
                            $details[] = array($globale->getBalise() => '<img src="' . $this->kernel->getProjectDir() . '/data/' . $globale->getCorrespondanceGlobale()->getValueLocal() . '"/>');
                        } else {
                            $details[] = array($globale->getBalise() => '<img src="' . $this->kernel->getProjectDir() . '/data/' . PSMF::NOT_FOUND_IMAGE . '"/>');
                        }
                    } else {
                        if ($format == PSMF::WORD) {
                            $details[] = array($globale->getBalise() => '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $globale->getCorrespondanceGlobale()->getValueLocal() . '"/>');
                        } else {
                            $details[] = array($globale->getBalise() => '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/origin/' . $globale->getCorrespondanceGlobale()->getValueLocal() . '"/>');
                        }
                    }
                    break;
                default:
                    $details[] = array($globale->getBalise() => $globale->getCorrespondanceGlobale()->getValueLocal());
                    break;
            }
        }

        foreach ($locales as $locale) {
            $correspondance = $this->correspondanceRepository->findOneBy(['psmf' => $psmf, 'variable' => $locale]);
            if ($correspondance) {
                switch ($locale->getType()->getCode()) {
                    case TypeVariable::IMAGE:
                        if ($format == PSMF::PDF) {
                            if (is_file($this->kernel->getProjectDir() . '/data/' . $correspondance->getValueLocal())) {
                                $details[] = array($locale->getBalise() => '<img src="' . $this->kernel->getProjectDir() . '/data/' . $correspondance->getValueLocal() . '"/>');
                            } else {
                                $details[] = array($locale->getBalise() => '<img src="' . $this->kernel->getProjectDir() . '/data/' . PSMF::NOT_FOUND_IMAGE . '"/>');
                            }
                        } else {
                            if ($format == PSMF::WORD) {
                                $details[] = array($locale->getBalise() => '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/' . $correspondance->getValueLocal() . '"/>');
                            } else {
                                $details[] = array($locale->getBalise() => '<img src="' . $request->getScheme() . '://' . $request->getHttpHost() . '/en/upload4word/' . $correspondance->getValueLocal() . '"/>');
                            }
                        }
                        break;
                    default:
                        $details[] = array($locale->getBalise() => $correspondance->getValueLocal());
                        break;
                }
            }
        }

        ksort($details);

        return $details;
    }

    private function str_replace_first($from, $to, $content)
    {
        $from = '/' . preg_quote($from, '/') . '/';

        return preg_replace($from, $to, $content, 1);
    }
}
