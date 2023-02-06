<?php

namespace App\Controller\TemplateManagement;

use App\Entity\TemplateManagement\Template;
use App\Entity\TemplateManagement\Section;
use App\Entity\PSMFManagement\PSMF;
use App\Manager\PSMFManagement\PSMFDocumentManager;
use App\Repository\TemplateManagement\SectionRepository;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\SimpleType\Jc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @codeCoverageIgnore
 * @Route("/admin/template")
 * test template word export
 */
class TemplateWordExportDebugController extends AbstractController
{
    protected $pSMFDocumentManager;
    protected $sectionRepository;
    protected $twig;

    public function __construct(PSMFDocumentManager $pSMFDocumentManager, SectionRepository $sectionRepository, Environment $twig)
    {
        $this->pSMFDocumentManager = $pSMFDocumentManager;
        $this->sectionRepository = $sectionRepository;
        $this->twig = $twig;
    }

    /**
     * word export test 1, native solution 
     * @Route("/wordExportTest1", name="admin_template_wordExportTest1", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function wordExportTest1(): Response
    {
        $contenu = $this->twig->render('TemplateManagement/WordExportTest/test1.html.twig');
        // create the response             
        $response = new Response(utf8_decode($contenu));
                
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-word; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=test1_' . (new \DateTime())->format('d-M-Y H:i:s') .'.doc');

        return $response; 
    }

    /**
     * word export test 2, phpword demo
     * @Route("/wordExportTest2", name="admin_template_wordExportTest2", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function wordExportTest2(): Response
    {
        $phpWord = new PhpWord();

        $section = $phpWord->addSection();

        // $header = $section->addHeader();
        // $header->addText(html_entity_decode("
        //     header", ENT_QUOTES | ENT_XML1, 'UTF-8'), ['size' => 14, 'color' => 'black', 'bold' => true, 'alignment' => Jc::CENTER]);

        // Add footer
        $footer = $section->addFooter();
        $footer->addText(html_entity_decode("footer", ENT_QUOTES | ENT_XML1, 'UTF-8'), null, ['alignment' => Jc::CENTER]);

        $html = '<h1>Adding element via HTML</h1>';
        $html .= '<p>Some well-formed HTML snippet needs to be used</p>';
        $html .= '<p>With for example <strong>some<sup>1</sup> <em>inline</em> formatting</strong><sub>1</sub></p>';

        $html .= '<p>A link to <a href="http://phpword.readthedocs.io/" style="text-decoration: underline">Read the docs</a></p>';

        $html .= '<p lang="he-IL" style="text-align: right; direction: rtl">היי, זה פסקה מימין לשמאל</p>';

        $html .= '<p style="margin-top: 240pt;">Unordered (bulleted) list:</p>';
        $html .= '<ul><li>Item 1</li><li>Item 2</li><ul><li>Item 2.1</li><li>Item 2.1</li></ul></ul>';

        $html .= '<p style="margin-top: 240pt;">1.5 line height with first line text indent:</p>';
        $html .= '<p style="text-align: justify; text-indent: 70.9pt; line-height: 150%;">Lorem ipsum dolor sit amet, <strong>consectetur adipiscing elit</strong>, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>';

        $html .= '<h2 style="align: center">centered title</h2>';

        $html .= '<p style="margin-top: 240pt;">Ordered (numbered) list:</p>';
        $html .= '<ol>
                        <li><p style="font-weight: bold;">List 1 item 1</p></li>
                        <li>List 1 item 2</li>
                        <ol>
                            <li>sub list 1</li>
                            <li>sub list 2</li>
                        </ol>
                        <li>List 1 item 3</li>
                    </ol>
                    <p style="margin-top: 15px;">A second list, numbering should restart</p>
                    <ol>
                        <li>List 2 item 1</li>
                        <li>List 2 item 2</li>
                        <li>
                            <ol>
                                <li>sub list 1</li>
                                <li>sub list 2</li>
                            </ol>
                        </li>
                        <li>List 2 item 3</li>
                        <ol>
                            <li>sub list 1, restarts with a</li>
                            <li>sub list 2</li>
                        </ol>
                    </ol>';

        $html .= '<p style="margin-top: 240pt;">List with formatted content:</p>';
        $html .= '<ul>
                        <li>
                            <span style="font-family: arial,helvetica,sans-serif;">
                                <span style="font-size: 16px;">big list item1</span>
                            </span>
                        </li>
                        <li>
                            <span style="font-family: arial,helvetica,sans-serif;">
                                <span style="font-size: 10px; font-weight: bold;">list item2 in bold</span>
                            </span>
                        </li>
                    </ul>';

        $html .= '<p style="margin-top: 240pt;">A table with formatting:</p>';
        $html .= '<table align="center" style="width: 100%; border: 6px #0000FF double;">
                        <thead>
                            <tr style="background-color: #FF0000; text-align: center; color: #FFFFFF; font-weight: bold; ">
                                <th style="width: 50pt">header a</th>
                                <th style="width: 50">header          b</th>
                                <th style="background-color: #FFFF00; border-width: 12px"><span style="background-color: #00FF00;">header c</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td style="border-style: dotted; border-color: #FF0000">1</td><td colspan="2">2</td></tr>
                            <tr><td>This is <b>bold</b> text</td><td></td><td>6</td></tr>
                        </tbody>
                    </table>';

        $html .= '<p style="margin-top: 240pt;">Table inside another table:</p>';
        $html .= '<table align="center" style="width: 100%; border: 6px #0000FF double;">
            <tr><td>
                <table style="width: 100%; border: 4px #FF0000 dotted;">
                    <tr><td>column 1</td><td>column 2</td></tr>
                </table>
            </td></tr>
            <tr><td style="text-align: center;">Cell in parent table</td></tr>
        </table>';

        $html .= '<p style="margin-top: 240pt;">The text below is not visible, click on show/hide to reveil it:</p>';
        $html .= '<p style="display: none">This is hidden text</p>';  
         
        Html::addHtml($section, $html, false, false);  

        //$section->addPageBreak();

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        $today = new \DateTime();

        $fileName = 'test2_' . $today->format('d-M-Y H:i:s') . '.docx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        //ecriture du fichier dans un chemin temporel
        $objWriter->save($temp_file);

        // transfert du fichier tempaire commme pièce jointe
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        return $response;          
    } 

    /**
     * word export test 21, phpword "Word a rencontré une erreur lors de l'ouverture du fichier"
     * @Route("/wordExportTest21", name="admin_template_wordExportTest21", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function wordExportTest21(): Response
    {
        $phpWord = new PhpWord();

        $section = $phpWord->addSection();

        // $header = $section->addHeader();
        // $header->addText(html_entity_decode("
        //     header", ENT_QUOTES | ENT_XML1, 'UTF-8'), ['size' => 14, 'color' => 'black', 'bold' => true, 'alignment' => Jc::CENTER]);

        // Add footer
        $footer = $section->addFooter();
        $footer->addText(html_entity_decode("footer", ENT_QUOTES | ENT_XML1, 'UTF-8'), null, ['alignment' => Jc::CENTER]);
         
        $html = '<table>
   <tbody>
      <tr>
         <td colspan="2" align="center"><span style="color:red">SYS_PART_TITRE</span></td>
      </tr>
      <tr>
         <td colspan="2" align="center" height="100"><span style="color:red">SYS_CLIENT_LOGO</span></td>
      </tr>
      <tr>
         <td colspan="2" align="center">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="2"><b>EudraVigilance system assigned number:</b>&nbsp;<span style="color:red">SYS_PSMF_EUDRA_NUMBER</span></td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="2"><b>Marketing Authorisation Holder:</b>&nbsp;<span style="color:red">SYS_CLIENT_NAME</span> <span style="color:red">SYS_CLIENT_ADRESS</span></td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="2"><b>EU QPPV name:</b>&nbsp;<span style="color:red">SYS_EUQPPV_LASTNAME</span></td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="2"><b>QPPV Third party company’s name: </b></td>
      </tr>
      <tr>
         <td colspan="2"><span style="color:green">{% if psmf.euqppvEntity.code == "um" %}</span><span style="color:red">SYS_EUQPPV_ADRESS</span> <span style="color:red">SYS_EUQPPV_COMPANY</span><span style="color:green">{% endif %}</span>
            <span style="color:green">{% if psmf.euqppvEntity.code == "presta" %}</span><span style="color:red">SYS_EUQPPV_ADRESS</span> <span style="color:red">SYS_EUQPPV_COMPANY</span><span style="color:green">{% endif %}</span>
            <span style="color:green">{% if psmf.euqppvEntity.code == "client" %}</span>Not applicable<span style="color:green">{% endif %}</span>
         </td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="2"><b>Date:</b>&nbsp;<span style="color:red">SYS_PSMF_LAST_MAJ</span></td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
         <td><span style="color:red">SYS_CONTACT_PV_CLIENT_FIRSTNAME</span></td>
         <td><span style="color:red">SYS_EUQPPV_FIRSTNAME</span></td>
      </tr>
      <tr>
         <td><span style="color:red">SYS_CONTACT_PV_CLIENT_LASTNAME</span></td>
         <td><span style="color:red">SYS_EUQPPV_LASTNAME</span></td>
      </tr>
      <tr>
         <td><span style="color:red">SYS_CONTACT_PV_CLIENT_FUNCTION</span></td>
         <td>EUQPPV {% if psmf.euQPPV.id == psmf.frRPV.id %}/ Pharmacovigilance contact person for France<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td valign="top"><span style="color:red">SYS_CLIENT_NAME</span></td>
         <td valign="top"><span style="color:red">SYS_EUQPPV_COMPANY</span></td>
      </tr>
   </tbody>
</table>
<table width="100%">
   <tbody>
      <tr>
         <td align="center"><span style="color:red">SYS_PART_TITRE</span></td>
      </tr>
   </tbody>
</table>
<br/>
<span style="color:red">SYS_TABLE_CONTENTS</span>
<table width="100%">
   <tbody>
      <tr>
         <td colspan="2" align="center"><span style="color:red">SYS_PART_TITRE</span></td>
      </tr>
      <tr>
         <td>ANSM</td>
         <td>Agence Nationale de Sécurité du Médicament et des produits de santé</td>
      </tr>
      <tr>
         <td>CAPA</td>
         <td>Corrective Action and Preventive Action</td>
      </tr>
      <tr>
         <td>DHPC</td>
         <td>Direct Healthcare Professional Communications</td>
      </tr>
      <tr>
         <td>E2B</td>
         <td>Format for Data Elements for transmission of Individual Case Safety Report</td>
      </tr>
      <tr>
         <td>EMA</td>
         <td>European Medicines Agency</td>
      </tr>
      <tr>
         <td>EU QPPV</td>
         <td>European Qualified Person for Pharmacovigilance </td>
      </tr>
      <tr>
         <td>EV</td>
         <td>EudraVigilance database </td>
      </tr>
      <tr>
         <td>GVP</td>
         <td>Good Pharmacovigilance Practices</td>
      </tr>
      <tr>
         <td>ICSR</td>
         <td>Individual Case Safety Report </td>
      </tr>
      <tr>
         <td>INN</td>
         <td>International Non-proprietary Name </td>
      </tr>
      <tr>
         <td>MAH</td>
         <td>Marketing Authorisation Holder </td>
      </tr>
      <tr>
         <td>MLM</td>
         <td>Medical Literature Monitoring  </td>
      </tr>
      <tr>
         <td>NCA</td>
         <td>National Competent Authority</td>
      </tr>
      <tr>
         <td>MedDRA</td>
         <td>Medical Dictionary for Regulatory Activities </td>
      </tr>
      <tr>
         <td>PBRER</td>
         <td>Periodic Risk Benefit Evaluation Report </td>
      </tr>
      <tr>
         <td>PIL</td>
         <td>Product Information Leaflet</td>
      </tr>
      <tr>
         <td>PSMF</td>
         <td>Pharmacovigilance System Master File </td>
      </tr>
      <tr>
         <td>PSUR</td>
         <td>Periodic Safety Update Report </td>
      </tr>
      <tr>
         <td>PV</td>
         <td>PharmacoVigilance </td>
      </tr>
      <tr>
         <td>Q&amp;A</td>
         <td>Question and Answer</td>
      </tr>
      <tr>
         <td>RMP</td>
         <td>Risk Management Plan </td>
      </tr>
      <tr>
         <td>SmPC</td>
         <td>Summary of Product Characteristics </td>
      </tr>
      <tr>
         <td>XML</td>
         <td>Format of the file for transmission eXtensible Markup Language</td>
      </tr>
   </tbody>
</table>
<table width="100%">
   <tbody>
      <tr>
         <td align="center"><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span></td>
      </tr>
   </tbody>
</table>
<br/>
<table width="100%">
   <tbody>
      <tr>
         <td align="center"><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span></td>
      </tr>
   </tbody>
</table>
<p>As per Module I of Good Pharmacovigilance Practices in force, the QPPV handles several responsibilities: </p>
<ul>
   <li>Establishment and maintenance of the marketing authorization holder’s marketing system; </li>
   <li>Promote maintain and improve compliance with the legal requirements; </li>
   <li>Having an overview of medicinal product safety profiles and any emerging safety concerns; </li>
   <li>Being aware of any conditions or obligations adopted as part of the marketing authorisations and other commitments relating to safety or the safe use of the products; </li>
   <li>Being aware of the risk minimisation measures; </li>
   <li>Being aware of and having sufficient authority over the content of risk management plans;</li>
   <li>Being involved in the review and sign-off of protocols of post-authorisation safety studies conducted in the EU or pursuant to a risk management plan agreed in the EU; </li>
   <li>Being aware of post-authorisation safety studies requested by a competent authority including the results of such studies; </li>
   <li>Ensuring the conduct of pharmacovigilance and the submission of all pharmacovigilance-related documents in accordance with the legal requirements and GVP; </li>
   <li>Ensuring the necessary quality, including the accuracy and completeness, of pharmacovigilance data submitted to the competent authorities in Members States and the Agency; </li>
   <li>Ensuring a full and prompt response to any request from the competent authorities in Members States and from the Agency for the provision of additional information necessary for the benefit/risk evaluation of a medicinal product; </li>
   <li>Providing any other information relevant to the benefit-risk evaluation to the competent authorities in Members States and the Agency; </li>
   <li>Providing input on the preparation of regulatory action in response to emerging safety concerns (e.g. variations, urgent safety restrictions, and communication to patients and healthcare professionals); </li>
   <li>Acting as a single pharmacovigilance contact point for the competent authorities in Member States and the Agency on a 24-hour basis as well as a contact point for pharmacovigilance inspections.</li>
</ul>
<p>This responsibility for the pharmacovigilance system means that the QPPV has oversight over the functioning of the system in all relevant aspects, including its quality system (e.g. standard operating procedures, contractual arrangements, database operations, compliance data regarding quality, completeness and timeliness of expedited reporting and submission of periodic safety update reports, audit reports and training of personnel in relation to pharmacovigilance). </p>
<p>Specifically, for the adverse reaction database, the QPPV is aware of the validation status of the database, including any failures that occurred during validation and the corrective actions that have been taken to address the failures. The QPPV is also informed of significant changes that are made to the database (e.g. changes that could have an impact on pharmacovigilance activities).</p>
<p>The QPPV may delegate specific tasks, under supervision, to appropriate qualified and trained individuals, for example, acting as safety experts for certain products, provided that the QPPV maintains system oversight and overview of the safety profiles of all products. Such delegation is documented. In addition, the QPPV ensures that all the staff involved in pharmacovigilance activities have received appropriate training.</p>
<span style="color:green">{% if psmf.euqppvEntity.code == "um" %}</span>They have the support of medically qualified staff for medical evaluations.<br/>The procedure describing these responsibilities is PR-VIG-UM-003 "Procédure sur la fonction QPPV".<span style="color:green">{% else %}</span><span style="color:red">LONGTEXT_MEDECIN_ACCESS</span><br/><span style="color:red">LONGTEXT_PROCEDURE_QPPV</span><span style="color:green">{% endif %}</span>
<br/><br/><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span>&nbsp;<br/>
<p><span style="color:red">SYS_EUQPPV_FIRSTNAME</span> <span style="color:red">SYS_EUQPPV_LASTNAME</span>, is employed by <span style="color:red">SYS_EUQPPV_COMPANY</span>&nbsp; and appointed by <span style="color:red">SYS_CLIENT_NAME</span> as its European Qualified Person for Pharmacovigilance (EU QPPV) for the products listed in Annex H. </p>
<p>With <span style="color:red">SYS_EUQPPV_HIS_HER</span> extensive experience in the pharmacovigilance field, <span style="color:red">SYS_EUQPPV_HE_SHE</span> has acquired adequate theoretical and practical knowledge for the performance of pharmacovigilance activities. <span style="color:red">LONGTEXT_EUQPPV_CV_DETAIL</span></p>
<p><span style="color:red">SYS_EUQPPV_HIS_HER</span> curriculum vitae and proof of registration with the EudraVigilance database are provided in Annex A. </p>
<br/><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span>&nbsp;<br/><span style="color:red">SYS_EUQPPV_FIRSTNAME</span> <span style="color:red">SYS_EUQPPV_LASTNAME</span><br/><span style="color:red">SYS_EUQPPV_COMPANY</span><br/><span style="color:red">SYS_EUQPPV_ADRESS</span><br/>
Tel: <span style="color:red">SYS_EUQPPV_FIX</span><br/>
Mobil:&nbsp; <span style="color:red">SYS_EUQPPV_MOBILE</span><br/>
Fax: <span style="color:red">SYS_EUQPPV_FAX</span>&nbsp;<br/>E-mail: <span style="color:red">SYS_EUQPPV_MAIL</span><br/>
<p>The QPPV can be joined at <span style="color:red">SYS_EUQPPV_MOBILE</span> the clock / 7 days a week.</p>
<br/><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span>&nbsp;<br/>
<p><span style="color:red">SYS_EUQPPV_FIRSTNAME</span> <span style="color:red">SYS_EUQPPV_LASTNAME</span> is responsible for the EU QPPV and RPV FR functions. <span style="color:green">{% if psmf.deputyEUQPPV %}</span> <span style="color:red">SYS_DEPUTY_EUQPPV_FIRSTNAME</span>&nbsp; <span style="color:red">SYS_DEPUTY_EUQPPV_LASTNAME</span> is Deputy EU QPPV and Deputy RPV FR for <span style="color:red">SYS_CLIENT_NAME</span>. They have the support of medically qualified staff for medical evaluations. <span style="color:green">{% else %}</span><span style="color:green">{% endif %}</span></p>
<span style="color:red">LONGTEXT_UM_DEPUTY_EUQPPV_INTERIM</span><br/>
<br/><br/><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span>&nbsp;<br/>
<p>Details about the contact person for pharmacovigilance nominated at a national level, as well as SYS_<span style="color:red">FR_RPV_HIS_HER</span> responsibilities and tasks can be found in Appendix A.</p>
<span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span>
<p><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span><br/></p>
<p><span style="color:red">SYS_PART_POSITION</span> <span style="color:red">SYS_PART_TITRE</span><br/></p>
<span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">SYS_PART_TITRE</span>
<br/><span style="color:red">SYS_EUQPPV_CV</span>
<br/><span style="color:green">{% if psmf.deputyEUQPPV %}</span><span style="color:red">SYS_DEPUTY_EUQPPV_CV</span><span style="color:green">{% endif %}</span><br/><span style="color:red">SYS_PART_TITRE</span><br/>
<table width="100%" border="1">
   <tbody>
      <tr>
         <th>Class</th>
         <th>Pharmacovigilance tasks (post marketing)</th>
         <th colspan="2" align="center">Location</th>
      </tr>
      <tr>
         <td></td>
         <td></td>
         <td><span style="color:red">SYS_CLIENT_NAME</span></td>
         <td>UM</td>
      </tr>
      <tr>
         <td valign="top">QPPV</td>
         <td valign="top">EU and local QPPV</td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_1 == "Client" or OPTION_TASK_QPPV_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_1 == "UM" or OPTION_TASK_QPPV_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Deputy EU-QPPV</td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_2 == "Client" or OPTION_TASK_QPPV_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_2 == "UM" or OPTION_TASK_QPPV_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>PSMF writing and updates</td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_3 == "Client" or OPTION_TASK_QPPV_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_3 == "UM" or OPTION_TASK_QPPV_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>SDEA writing</td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_4 == "Client" or OPTION_TASK_QPPV_4 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_4 == "UM" or OPTION_TASK_QPPV_4 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>SDEA review and approval</td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_5 == "Client" or OPTION_TASK_QPPV_5 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_QPPV_5 == "UM" or OPTION_TASK_QPPV_5 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>On call duty</td>
         <td>Pharmacovigilance and medical information</td>
         <td><span style="color:green">{% if OPTION_TASK_ON_CALL_DUTY_1 == "Client" or OPTION_TASK_ON_CALL_DUTY_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ON_CALL_DUTY_1 == "UM" or OPTION_TASK_ON_CALL_DUTY_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Other calls</td>
         <td><span style="color:green">{% if OPTION_TASK_ON_CALL_DUTY_2 == "Client" or OPTION_TASK_ON_CALL_DUTY_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ON_CALL_DUTY_2 == "UM" or OPTION_TASK_ON_CALL_DUTY_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>ICSR</td>
         <td>Collection of cases</td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_1 == "Client" or OPTION_TASK_ICSR_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_1 == "UM" or OPTION_TASK_ICSR_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Evaluation of cases</td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_2 == "Client" or OPTION_TASK_ICSR_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_2 == "UM" or OPTION_TASK_ICSR_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Safety database case entry</td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_3 == "Client" or OPTION_TASK_ICSR_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_3 == "UM" or OPTION_TASK_ICSR_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Case referencing, communication and investigation with reporter</td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_4 == "Client" or OPTION_TASK_ICSR_4 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_4 == "UM" or OPTION_TASK_ICSR_4 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Approbation, coding and classification, imputability</td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_5 == "Client" or OPTION_TASK_ICSR_5 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_5 == "UM" or OPTION_TASK_ICSR_5 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Submission, reporting through/in Eudravigilance</td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_6 == "Client" or OPTION_TASK_ICSR_6 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_6 == "UM" or OPTION_TASK_ICSR_6 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Medical assessment</td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_7 == "Client" or OPTION_TASK_ICSR_7 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_7 == "UM" or OPTION_TASK_ICSR_7 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>EudraVigilance L2A Request</td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_8 == "Client" or OPTION_TASK_ICSR_8 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_8 == "UM" or OPTION_TASK_ICSR_8 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Periodic reconciliation</td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_9 == "Client" or OPTION_TASK_ICSR_9 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_ICSR_9 == "UM" or OPTION_TASK_ICSR_9 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>SIGNAL DETECTION AND ANALYSIS</td>
         <td>Periodic signal detection/validation</td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_1 == "Client" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_1 == "UM" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Cumulative signal detection/validation</td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_2 == "Client" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_2 == "UM" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Quarterly Overall evaluation (safety committee)</td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_3 == "Client" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_3 == "UM" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Prioritisation of signal detected</td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_4 == "Client" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_4 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_4 == "UM" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_4 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Further assessment of signal, recommendations for actions</td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_5 == "Client" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_5 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_5 == "UM" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_5 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Notification of emerging safety issues</td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_6 == "Client" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_6 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_6 == "UM" or OPTION_TASK_SIGNAL_DETECTION_AND_ANALYSIS_6 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>RISK MANAGEMENT PLAN</td>
         <td>RMP writing and approval</td>
         <td><span style="color:green">{% if OPTION_TASK_RISK_MANAGEMENT_PLAN_1 == "Client" or OPTION_TASK_RISK_MANAGEMENT_PLAN_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_RISK_MANAGEMENT_PLAN_1 == "UM" or OPTION_TASK_RISK_MANAGEMENT_PLAN_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Approval and Filing to Authorities</td>
         <td><span style="color:green">{% if OPTION_TASK_RISK_MANAGEMENT_PLAN_2 == "Client" or OPTION_TASK_RISK_MANAGEMENT_PLAN_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_RISK_MANAGEMENT_PLAN_2 == "UM" or OPTION_TASK_RISK_MANAGEMENT_PLAN_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>LITERATURE</td>
         <td>MLM literature monitoring</td>
         <td><span style="color:green">{% if OPTION_TASK_LITERATURE_1 == "Client" or OPTION_TASK_LITERATURE_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_LITERATURE_1 == "UM" or OPTION_TASK_LITERATURE_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Local non indexed literature monitoring</td>
         <td><span style="color:green">{% if OPTION_TASK_LITERATURE_2 == "Client" or OPTION_TASK_LITERATURE_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_LITERATURE_2 == "UM" or OPTION_TASK_LITERATURE_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>International literature monitoring (Embase, Medline)</td>
         <td><span style="color:green">{% if OPTION_TASK_LITERATURE_3 == "Client" or OPTION_TASK_LITERATURE_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_LITERATURE_3 == "UM" or OPTION_TASK_LITERATURE_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>PSUR</td>
         <td>Monitoring of schedule</td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_1 == "Client" or OPTION_TASK_PSUR_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_1 == "UM" or OPTION_TASK_PSUR_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Sales data, official documentation, MA, SmPC, leaflet</td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_2 == "Client" or OPTION_TASK_PSUR_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_2 == "UM" or OPTION_TASK_PSUR_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>PSUR writing</td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_3 == "Client" or OPTION_TASK_PSUR_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_3 == "UM" or OPTION_TASK_PSUR_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Quality control on PSUR</td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_4 == "Client" or OPTION_TASK_PSUR_4 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_4 == "UM" or OPTION_TASK_PSUR_4 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Approval and Submission to Authorities</td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_5 == "Client" or OPTION_TASK_PSUR_5 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_PSUR_5 == "UM" or OPTION_TASK_PSUR_5 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>STUDY MANAGEMENT</td>
         <td>Pre-authorization</td>
         <td><span style="color:green">{% if OPTION_TASK_STUDY_MANAGEMENT_1 == "Client" or OPTION_TASK_STUDY_MANAGEMENT_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_STUDY_MANAGEMENT_1 == "UM" or OPTION_TASK_STUDY_MANAGEMENT_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Post-authorization</td>
         <td><span style="color:green">{% if OPTION_TASK_STUDY_MANAGEMENT_2 == "Client" or OPTION_TASK_STUDY_MANAGEMENT_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_STUDY_MANAGEMENT_2 == "UM" or OPTION_TASK_STUDY_MANAGEMENT_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>SAFETY DATABASE</td>
         <td>Data base administration, validation</td>
         <td><span style="color:green">{% if OPTION_TASK_SAFETY_DATABASE_1 == "Client" or OPTION_TASK_SAFETY_DATABASE_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_SAFETY_DATABASE_1 == "UM" or OPTION_TASK_SAFETY_DATABASE_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>REGULATION</td>
         <td>Weekly regulatory watch</td>
         <td><span style="color:green">{% if OPTION_TASK_REGULATION_1 == "Client" or OPTION_TASK_REGULATION_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_REGULATION_1 == "UM" or OPTION_TASK_REGULATION_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>XEVMPD</td>
         <td>Populate Product and SmPC updates</td>
         <td><span style="color:green">{% if OPTION_TASK_XEVMPD_1 == "Client" or OPTION_TASK_XEVMPD_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_XEVMPD_1 == "UM" or OPTION_TASK_XEVMPD_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>QUALITY SYSTEM</td>
         <td>Handle documentation, SOP, audit, inspection and CAPA</td>
         <td><span style="color:green">{% if OPTION_TASK_QUALITY_SYSTEM_1 == "Client" or OPTION_TASK_QUALITY_SYSTEM_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_QUALITY_SYSTEM_1 == "UM" or OPTION_TASK_QUALITY_SYSTEM_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Provide Key Performance Indicators</td>
         <td><span style="color:green">{% if OPTION_TASK_QUALITY_SYSTEM_2 == "Client" or OPTION_TASK_QUALITY_SYSTEM_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_QUALITY_SYSTEM_2 == "UM" or OPTION_TASK_QUALITY_SYSTEM_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Approve Key Performance Indicators</td>
         <td><span style="color:green">{% if OPTION_TASK_QUALITY_SYSTEM_3 == "Client" or OPTION_TASK_QUALITY_SYSTEM_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_QUALITY_SYSTEM_3 == "UM" or OPTION_TASK_QUALITY_SYSTEM_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>TRAINING</td>
         <td>Training of company personal as appropriate</td>
         <td><span style="color:green">{% if OPTION_TASK_TRAINING_1 == "Client" or OPTION_TASK_TRAINING_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_TRAINING_1 == "UM" or OPTION_TASK_TRAINING_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Training of Sales representatives</td>
         <td><span style="color:green">{% if OPTION_TASK_TRAINING_2 == "Client" or OPTION_TASK_TRAINING_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_TRAINING_2 == "UM" or OPTION_TASK_TRAINING_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>INFORMATION</td>
         <td>Provide reference medical and pharmaceutical information on products</td>
         <td><span style="color:green">{% if OPTION_TASK_INFORMATION_1 == "Client" or OPTION_TASK_INFORMATION_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_INFORMATION_1 == "UM" or OPTION_TASK_INFORMATION_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Response to inquiries according to reference information</td>
         <td><span style="color:green">{% if OPTION_TASK_INFORMATION_2 == "Client" or OPTION_TASK_INFORMATION_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_INFORMATION_2 == "UM" or OPTION_TASK_INFORMATION_2 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td></td>
         <td>Response to inquiries other than included in reference information</td>
         <td><span style="color:green">{% if OPTION_TASK_INFORMATION_3 == "Client" or OPTION_TASK_INFORMATION_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_INFORMATION_3 == "UM" or OPTION_TASK_INFORMATION_3 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>MA</td>
         <td>Handle Safety variation of Marketing Authorization</td>
         <td><span style="color:green">{% if OPTION_TASK_MA_1 == "Client" or OPTION_TASK_MA_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_MA_1 == "UM" or OPTION_TASK_MA_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>PACKAGING COMPONENTS</td>
         <td>Update packaging components i.e. patient leaflet</td>
         <td><span style="color:green">{% if OPTION_TASK_PACKAGING_COMPONENTS_1 == "Client" or OPTION_TASK_PACKAGING_COMPONENTS_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_PACKAGING_COMPONENTS_1 == "UM" or OPTION_TASK_PACKAGING_COMPONENTS_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
      <tr>
         <td>COMMUNICATION</td>
         <td>Handle communication to Health Professionals and Patients</td>
         <td><span style="color:green">{% if OPTION_TASK_COMMUNICATION_1 == "Client" or OPTION_TASK_COMMUNICATION_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
         <td><span style="color:green">{% if OPTION_TASK_COMMUNICATION_1 == "UM" or OPTION_TASK_COMMUNICATION_1 == "Les deux" %}</span>X<span style="color:green">{% endif %}</span></td>
      </tr>
   </tbody>
</table>
<br/><span style="color:red">SYS_PART_TITRE</span><br/>
<table width="100%" border="1">
   <tbody>
      <tr style="background-color:#CCCCCC">
         <th>EU QPPV</th>
         <th><span style="color:green">{% if psmf.deputyEUQPPV %}</span>Deputy EU QPPV <span style="color:green">{% endif %}</span></th>
      </tr>
      <tr>
         <td><span style="color:red">SYS_EUQPPV_LASTNAME</span> <span style="color:red">SYS_EUQPPV_FIRSTNAME</span>&nbsp;<br/><span style="color:red">SYS_EUQPPV_COMPANY</span><br/><span style="color:red">SYS_EUQPPV_ADRESS</span><br/><br/>
            Tel: <span style="color:red">SYS_EUQPPV_FIX</span><br/> 
            Mobil: <span style="color:red">SYS_EUQPPV_MOBILE</span><br/>
            Fax: <span style="color:red">SYS_EUQPPV_FAX</span><br/>
            e-mail: <span style="color:red">SYS_EUQPPV_MAIL</span><br/>
         </td>
         <td><span style="color:green">{% if psmf.deputyEUQPPV %}</span><span style="color:red">SYS_DEPUTY_EUQPPV_LASTNAME</span> <span style="color:red">SYS_DEPUTY_EUQPPV_FIRSTNAME</span><br/>
            <span style="color:red">SYS_DEPUTY_EUQPPV_COMPANY</span><br/><span style="color:red">SYS_DEPUTY_EUQPPV_ADRESS</span><br/><br/>
            Tel: <span style="color:red">SYS_DEPUTY_EUQPPV_FIX</span><br/>
            Mobil: <span style="color:red">SYS_DEPUTY_EUQPPV_MOBILE</span><br/>
            Fax: <span style="color:red">SYS_DEPUTY_EUQPPV_FAX</span><br/>
            e-mail: <span style="color:red">SYS_DEPUTY_EUQPPV_MAIL</span><br/><span style="color:green">{% endif %}</span>
         </td>
      </tr>
   </tbody>
</table>
<br/><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">IMG_EUDRAVIG_REGISTRATION</span><br/><span style="color:red">SYS_PART_TITRE</span><br/><u>
France</u><br/><span style="color:red">SYS_CLIENT_NAME</span> has nominated SYS_<span style="color:red">FR_RPV_FIRSTNAME</span> SYS_<span style="color:red">FR_RPV_LASTNAME</span>, Person responsible for Pharmacovigilance in France. The French Health Authority was informed of this nomination by post on <span style="color:red">DATE_FR_QPPV_NOMINATION</span>.<br/>
<br/>
Contact details are : <br/>
Name SYS_<span style="color:red">FR_RPV_FIRSTNAME</span> SYS_<span style="color:red">FR_RPV_LASTNAME</span><br/>
Function SYS_<span style="color:red">FR_RPV_FUNCTION</span><br/>
Address SYS_<span style="color:red">FR_RPV_ADRESS</span><br/>
Telephone SYS_<span style="color:red">FR_RPV_FIX</span><br/>
Fax SYS_<span style="color:red">FR_RPV_FAX</span><br/>
e-mail SYS_<span style="color:red">FR_RPV_MAIL</span><br/>
<br/>
Missions are :<br/>
<ul>
   <li>collect, process and make available the information on suspected adverse reactions due to medicinal products operated by <span style="color:red">SYS_CLIENT_NAME</span> to any authorised person and the people who make information through canvassing or prospecting for medicinal products;</li>
   <li>
      set up and manage the pharmacovigilance system and risk management system;<br/>
      <ul>
         <li>all suspected serious adverse reactions, which occurred in a Member State of the European Union or a State party to the agreement on the European Economic Area or a third country, which he has knowledge, without delay and at the latest within fifteen days of receipt of the information; </li>
         <li>all suspected non-serious adverse reactions, which occurred in a Member State of the European Union or a State party to the Agreement on the European Economic Area, which it has knowledge, within the ninety days following the receiving information; </li>
         <li>periodic safety update report (PSUR);</li>
      </ul>
   </li>
   <li>ensure the implementation and monitoring of the measures described in the European risk management plan at national level as well as specific measures on the national territory requested by the ANSM, such as enhanced surveillance or risk reduction activities, and also monitor the results of risk reduction measures;</li>
   <li>ensure the implementation of procedures to obtain accurate and verifiable data for the realisation of the scientific evaluation of suspected adverse reaction reports, collect additional information on these reports and send the updates to the Eudravigilance database;</li>
   <li>ensure that it is answered fully and promptly to the demands of the ANSM, Pharmacovigilance Regional Center and Center for Evaluation and Information on Drug Dependence (CEIP-A);</li>
   <li>implement the necessary measures for the detection and validation of signals and cooperate in the evaluation of a confirmed signal in accordance with the modalities described in Module IX of the GVP including the estimation of the incidence of suspected adverse reactions (or otherwise the rate of notifications);</li>
   <li>to have the elements guaranteeing SYS_<span style="color:red">FR_RPV_HIM_HER</span> the control of the computerized systems used in the framework of the execution of the activities of pharmacovigilance, their validation and their maintenance in the validated state;</li>
   <li>provide to the ANSM any other information relevant to the evaluation of benefice-risks related to a medicinal product, including both positive and negative results of biomedical research and studies of safety and effectiveness for all indications and populations, whether or not mentioned in the marketing authorisation, as well as data regarding any improper use of medication under the Marketing Authorisation and all information relating to sales volume and prescription for the medicinal product concerned.</li>
</ul>
<br/>
According to the French Health Authorities, the Accountable pharmacist of <span style="color:red">SYS_CLIENT_NAME</span> is responsible for all the PV activities from a legal point of view. Nevertheless. He has delegated the responsibility local Qualified Person for Pharmacovigilance for the products listed in Annex H.
<br/><span style="color:red">IMG_ANSM_LETTRE</span><br/><span style="color:red">LONGTEXT_A6_PAYS</span>
<p><span style="color:red">SYS_PART_TITRE</span></p>
<p>bla bla</p>
<br/><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">LONGTEXT_CONTRACTS_AGREEMENTS</span><span style="color:red">SYS_PART_TITRE</span><span style="color:red">SYS_PART_TITRE</span><br/>
Safety databases and computerized systems:<br/><span style="color:red">LONGTEXT_TAB_SAFETYBASE</span><br/>
<br/>
Organization of computer systems of <span style="color:red">SYS_CLIENT_NAME</span>.<br/><span style="color:red">LONGTEXT_TAB_CLIENT_COMPUTER_SYS</span><br/>
<br/><u>
Organization of computer systems of</u>.<br/>
<table width="100%" border="1">
   <tbody>
      <tr>
         <th>Name</th>
         <th>Function</th>
      </tr>
      <tr>
         <td>EOLE-DC01</td>
         <td>Work in direct</td>
      </tr>
      <tr>
         <td>???</td>
         <td>Local and remote daily backup</td>
      </tr>
   </tbody>
</table>
<span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">LONGTEXT_TAB_CLIENT_DOC</span><br/><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">LONGTEXT_TAB_DATABASE_UM_DOC</span><br/><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">LONGTEXT_TAB_DATABASE_DOC</span><br/><span style="color:red">SYS_PART_TITRE</span>
<br/><span style="color:red">LONGTEXT_AUTRES_ANNEXE_E</span><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">SYS_PART_TITRE</span><br/>
<table width="100%" border="1">
   <tbody>
      <tr style="background-color:#cccccc">
         <th>Indicators</th>
         <th>Target</th>
         <th>Frequency</th>
      </tr>
      <tr>
         <td>Serious cases submitted to EudraVigilance within 15 days after the receipt date</td>
         <td>100%</td>
         <td>Quarterly</td>
      </tr>
      <tr>
         <td>Non serious cases submitted to EudraVigilance within 90 days after the receipt date</td>
         <td>100%</td>
         <td>Quarterly</td>
      </tr>
      <tr>
         <td>Answer to requests from National Competent Authority or EMA within the timelines  </td>
         <td>100%</td>
         <td>Quarterly</td>
      </tr>
      <tr>
         <td>Safety variation submitted within the timelines</td>
         <td>100%</td>
         <td>Quarterly</td>
      </tr>
      <tr>
         <td>PSUR submitted to competent authorities within the timelines</td>
         <td>100%</td>
         <td>Quarterly</td>
      </tr>
   </tbody>
</table>
<br/><span style="color:red">SYS_PART_TITRE</span>
<table width="100%" border="1">
   <tbody>
      <tr style="background-color:#cccccc">
         <th>Indicators</th>
         <th>2018</th>
         <th>2019</th>
         <th>2020*</th>
      </tr>
      <tr>
         <td>Serious cases submitted to EudraVigilance within 15 days after the receipt date</td>
         <td><span style="color:red">INT_KPI1_YEAR_2</span></td>
         <td><span style="color:red">INT_KPI1_YEAR_1</span></td>
         <td><span style="color:red">INT_KPI1_YEAR_0</span></td>
      </tr>
      <tr>
         <td>Non serious cases submitted to EudraVigilance within 90 days after the receipt date</td>
         <td><span style="color:red">INT_KPI2_YEAR_2</span></td>
         <td><span style="color:red">INT_KPI2_YEAR_1</span></td>
         <td><span style="color:red">INT_KPI2_YEAR_0</span></td>
      </tr>
      <tr>
         <td>Answer to requests from National Competent Authority or EMA in the timelines  </td>
         <td><span style="color:red">INT_KPI3_YEAR_2</span></td>
         <td><span style="color:red">INT_KPI3_YEAR_1</span></td>
         <td><span style="color:red">INT_KPI3_YEAR_0</span></td>
      </tr>
      <tr>
         <td>PSUR submitted within the timelines  </td>
         <td><span style="color:red">INT_KPI4_YEAR_2</span></td>
         <td><span style="color:red">INT_KPI4_YEAR_1</span></td>
         <td><span style="color:red">INT_KPI4_YEAR_0</span></td>
      </tr>
   </tbody>
</table>
*Data lock point : <span style="color:red">DATE_KPI</span>&lt;<span style="color:red">SYS_PART_TITRE</span>&gt;<br/><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">LONGTEXT_AUDIT_LAST_LISTE</span><br/>
*Awaiting report; CAPA are to be agreed; CAPA (Corrective Action and Preventive Action) in process; CAPA implemented; Closed (no significant finding)<br/><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">LONGTEXT_AUDIT_FUTURE_LISTE</span><br/>
Note: doit contenir les audits internes couvrant activités PV et d’intérêt pour la PV, les audits externes de tous les partenaires.<br/>
Exemple de « Fields of audits »<br/>
Pharmacovigilance subcontracted activities<br/>
Regulatory and pharmacovigilance subcontracted activities<br/>
Import / Export and Distribution subcontracted activities including PV<br/>
Medical representative subcontracted activities including PV<br/>
Export promotion subcontracted activities including PV<br/><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">LONGTEXT_TAB_LIST_PRODUCT</span><br/>
<br/>
* Including Iceland, Liechtenstein, Norway<br/>
**: Centrally authorised, nationally authorised products, including those authorised through the mutual recognition or the decentralised procedure. <br/>
If applicable, the Rapporteur country or Reference Member State<br/>
#: List of countries and if applicable specify the authorization number, registration or visa.<br/><span style="color:red">SYS_PART_TITRE</span><br/><span style="color:red">SYS_PART_TITRE</span><br/>
<span style="color:red">HISTORIQUE_VESRION_MAIN_BODY_MOINS_5ANS</span><br/><span style="color:red">SYS_PART_TITRE</span><br/>
<span style="color:red">HISTORIQUE_VESRION_PSMF_ANNEXES_MOINS_5ANS</span>';
         
        Html::addHtml($section, $html, false, false);  

        //$section->addPageBreak();

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        $today = new \DateTime();

        $fileName = 'test21_' . $today->format('d-M-Y H:i:s') . '.docx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        //ecriture du fichier dans un chemin temporel
        $objWriter->save($temp_file);

        // transfert du fichier tempaire commme pièce jointe
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        return $response;          
    } 

    /**
     * word export test 3, phpword solution no march pas !!!!
     * @Route("/wordExportTest3", name="admin_template_wordExportTest3", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function wordExportTest3(): Response
    {
        $format = "word";

        $header = $this->sectionRepository->find(Section::HEADER_ID);
        $footer = $this->sectionRepository->find(Section::FOOTER_ID);
        $sections = $this->sectionRepository->findBy([
                    'template' => Template::TEMPLATE_ID,
                    'parent' => NULL,
                    'isValid' => true,
                    'isDeleted' => false
                ],[
                    'position' => 'ASC',
                ]);

        foreach ($sections as $s) {
            if ($s->getId() > 2) {
                $contenu .= $this->pSMFDocumentManager->sectionParser(null, $s->getContenu());
                foreach ($s->getSections() as $subSection) {
                    if ($subSection->getIsValid()) {
                        $contenu .= "<br/>";
                        $contenu .= $this->pSMFDocumentManager->sectionParser(null, $subSection->getContenu());
                        foreach ($subSection->getSections() as $_subSection) {
                          if ($_subSection->getIsValid()) {
                              $contenu .= "<br/>";
                              $contenu .= $this->pSMFDocumentManager->sectionParser(null, $_subSection->getContenu());
                          }                      
                        }
                    }
                }
            }
        }  

        $contenu = $this->pSMFDocumentManager->systemesParser(null, $contenu, $format);
        $contenu = $this->pSMFDocumentManager->globalesParser($contenu, $format, false);
        $contenu = $this->pSMFDocumentManager->qualiosParser($contenu, false);
        $contenu = $this->pSMFDocumentManager->localesParser(null, $contenu, $format); 
        $contenu = $this->pSMFDocumentManager->tableContentsParser($sections, $contenu, false);
        $contenu = $this->pSMFDocumentManager->logiqueParser(null, $contenu);
        //<br>
        $contenu = str_replace(["<br>", ], ["<br/>"], $contenu);
        var_dump($contenu); exit();
        $phpWord = new PhpWord();

        $section = $phpWord->addSection();

        // $header = $section->addHeader();
        // $header->addText(html_entity_decode("
        //     header", ENT_QUOTES | ENT_XML1, 'UTF-8'), ['size' => 14, 'color' => 'black', 'bold' => true, 'alignment' => Jc::CENTER]);

        // Add footer
        $footer = $section->addFooter();
        $footer->addText(html_entity_decode($footer->getContenu(), ENT_QUOTES | ENT_XML1, 'UTF-8'), null, ['alignment' => Jc::CENTER]);
         
        Html::addHtml($section, $contenu, false, false);  

        //$section->addPageBreak();

        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');

        $today = new \DateTime();

        $fileName = 'test2_' . $today->format('d-M-Y H:i:s') . '.docx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        //ecriture du fichier dans un chemin temporel
        $objWriter->save($temp_file);

        // transfert du fichier tempaire commme pièce jointe
        $response = new BinaryFileResponse($temp_file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);

        return $response;          
    }         
}
