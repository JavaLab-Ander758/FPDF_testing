<?php
require_once 'base.php';

/*******************************************************************************
 * PDF_emne - Extended from FPDF (http://www.fpdf.org/)                         *
 *                                                                              *
 * Version: 1.0                                                                 *
 * Creation time:   2020-3-30                                                   *
 * Last change:     2020-04-04 02:40                                            *
 * Author:  Anders Rubach Ese                                                   *
 *******************************************************************************/
class PDF_emne extends FPDF
{
    // Fixed Variables
    private $LOGO_IMAGE_ASSET_PATH = 'classes/PDF/fpdf182/assets_pdf/Uit_Logo_Bok_Sort_Minimized.jpg';
    private $IMAGE_TITLE_SIZE = 40;
    private $TABLE_HEIGHT = 6;
    private $LARGE_FONT_SIZE = 16;
    private $DEFAULT_FONT_SIZE = 10;
    private $STRING_WIDTH_FACTOR = 12 * 0.2;
    private $SMALL_FONT_SIZE = 9;
    private $DEFAULT_FONT = 'Arial';
    private $STRIKETHROUGH_FACTOR = 1.6;
    private $yValUnderHeader;

    // Colors
    private $LIGHT_GREY = 242;
    private $DARK_GREY = array(217, 217, 217);
    private $MONGO_RED = array(255, 87, 87);
    private $PLAIN_WHITE = array(255, 255, 255);
    private $DISCO_BLUE = array(68, 192, 226);

    // Variables
    protected $yValue = 0;

    // Construct parameters
    private Emne $emne;
    private $languageCode;

    // Inheritance
    private UtilsPDF $utils;

    /**
     * PDF constructor
     * @param Emne $emne
     * @param int $languageCode
     */
    public function __construct(Emne $emne, int $languageCode)
    {
        // Override parent
        parent::__construct();
        parent::AliasNbPages();
        parent::SetAuthor("runtime-terror");
        parent::SetTitle($emne->getEmneNavn($languageCode) . ' (' . $emne->getEmneKode() . ').pdf');
        $this->emne = $emne;
        $this->languageCode = $languageCode;
        $this->utils = new UtilsPDF($languageCode);
        $this->setPdfVariables();
    }

    // Page header
    function Header()
    {
        // Logo
        $this->Image($this->LOGO_IMAGE_ASSET_PATH, 1, 10, $this->IMAGE_TITLE_SIZE);
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);
        // Move to the right
        $this->Cell(40);
        // Title
        $cachedY = $this->postIncYValue($this->GetPageHeight() - $this->getImageLogoHeightResizeFactor());
        $this->setY(15);
        $this->setX(50);
        parent::SetFillColor($this->DARK_GREY[0], $this->DARK_GREY[1], $this->DARK_GREY[2]);
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->LARGE_FONT_SIZE);
        $this->Cell(140, 10, $this->getTitle(), 1, 0, 'C', true);
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);


        // Timestamp
        $this->setX(0);
        $this->setY(5);
        $this->Cell(0, 0, $this->getExtractionTimeStampAsString());

        // Ansvarlig fakultet
        parent::SetFillColor($this->MONGO_RED[0], $this->MONGO_RED[1], $this->MONGO_RED[2]);
        $str_length = strlen($this->emne->getAnsvarligFakultatToString()) * $this->STRING_WIDTH_FACTOR + $this->TABLE_HEIGHT;
        $this->setX(parent::GetPageWidth() - $str_length);
        $this->Cell($str_length, $this->TABLE_HEIGHT, $this->utils->handleISO($this->emne->getAnsvarligFakultatToString()), 1, 0, 'L', true);

        // Line break
        $this->Ln(20);
    }

    // Body
    public function setPdfVariables()
    {
        $this->AddPage();
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Static values
        $TABLE_HEIGHT = $this->TABLE_HEIGHT;
        $EXTRA_HEIGHT = $TABLE_HEIGHT / 2;
        $str_factor = $this->STRING_WIDTH_FACTOR;
        $width_factor = $TABLE_HEIGHT;
        $utils = $this->utils;
        $emne = $this->emne;

        parent::SetFillColor($this->LIGHT_GREY, $this->LIGHT_GREY, $this->LIGHT_GREY);
        // EmneKode
        $this->setY($this->postIncYValue($TABLE_HEIGHT + $EXTRA_HEIGHT));
        $this->yValUnderHeader = $this->getY();
        $this->Cell($this->getStrPxWdth($utils->EmneKodeAsString()), $this->TABLE_HEIGHT, $utils->EmneKodeAsString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($emne->getEmneKode()), $TABLE_HEIGHT, $emne->getEmneKode(), 1, 0, 'L');

        // EmneNivå
        $this->setX($this->getX() + $width_factor);
        $this->Cell($this->getStrPxWdth($utils->EmneNivaAsString()), $TABLE_HEIGHT, $utils->EmneNivaAsString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($emne->getNivaaStatusToString()), $TABLE_HEIGHT, $emne->getNivaaStatusToString(), 1, 0, 'L');

        // EmneNavn multi-language
        $this->setY($this->postIncYValue($TABLE_HEIGHT + $EXTRA_HEIGHT));
        $this->Cell($this->getStrPxWdth($utils->EmneNavnNorwegianToString()), $TABLE_HEIGHT, $utils->EmneNavnNorwegianToString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($emne->getEmneNavn(0)), $TABLE_HEIGHT, $emne->getEmneNavn(0), 1, 0, 'L');
        $this->setY($this->postIncYValue($TABLE_HEIGHT));
        $this->Cell($this->getStrPxWdth($utils->EmneNavnNynorskToString()), $TABLE_HEIGHT, $utils->EmneNavnNynorskToString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($emne->getEmneNavn(1)), $TABLE_HEIGHT, $emne->getEmneNavn(1), 1, 0, 'L');
        $this->setY($this->postIncYValue($TABLE_HEIGHT));
        $this->Cell($this->getStrPxWdth($utils->EmneNavnEnglishToString()), $TABLE_HEIGHT, $utils->EmneNavnEnglishToString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($emne->getEmneNavn(2)), $TABLE_HEIGHT, $emne->getEmneNavn(2), 1, 0, 'L');

        // Beskrivelse
        $this->setY($this->postIncYValue($TABLE_HEIGHT + $EXTRA_HEIGHT));
        $this->Cell($this->getStrPxWdth($utils->BeskrivelseToString()), $TABLE_HEIGHT, $utils->BeskrivelseToString(), 1, 0, 'L', true);
        $this->setY($this->postIncYValue($TABLE_HEIGHT));
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->SMALL_FONT_SIZE);
        $this->MultiCell(parent::GetPageWidth() - $this->rMargin * 2, 6, $emne->getBeskrivelse(), 1, 'L');
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Semesterår
        $this->setY($this->postIncYValue($TABLE_HEIGHT * 4));
        $this->Cell($this->getStrPxWdth($utils->SemesterAsString()), $TABLE_HEIGHT, $utils->SemesterAsString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($emne->getAar()), $TABLE_HEIGHT, $emne->getAar(), 1, 0, 'L');

        // Læringsformer og aktiviteter
        $this->setY($this->postIncYValue($TABLE_HEIGHT + $EXTRA_HEIGHT));
        $this->Cell($this->getStrPxWdth($utils->LaeringOgAktiviteterAsString()), $TABLE_HEIGHT, $utils->LaeringOgAktiviteterAsString(), 1, 0, 'L', true);
        $this->setY($this->postIncYValue($TABLE_HEIGHT));
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->SMALL_FONT_SIZE);
        $this->MultiCell(parent::GetPageWidth() - $this->rMargin * 2, 6, $utils->handleISO($emne->getLaeringsformerOgAktiviteter()), 1, 'L');
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Undervisningstermin
        $this->setY($this->postIncYValue($TABLE_HEIGHT * 6));
        $cachedY = $this->getY();
        $this->Cell($this->getStrPxWdth($utils->TerminAsString()), $TABLE_HEIGHT, $utils->TerminAsString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($utils->VaarAsString()), $TABLE_HEIGHT, $utils->VaarAsString(), 0, 0, 'L');
        $this->SetFont('ZapfDingBats', '', $this->DEFAULT_FONT_SIZE);
        $cachedX = $this->getX();
        $this->setY($this->postIncYValue($TABLE_HEIGHT / 3));
        $cachedMidY = $this->getY();
        $this->setX($cachedX);
        $this->Cell($TABLE_HEIGHT / 2, $TABLE_HEIGHT / 2, !empty($emne->getTerminStatus() == 1 or $emne->getTerminStatus() == 2) ? '4' : '', 1, 0);
        $cachedX = $this->getX() + $TABLE_HEIGHT / 2;
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);
        $this->setY($cachedMidY - ($TABLE_HEIGHT / 2) / 2);
        $this->setX($cachedX);
        $this->Cell($this->getStrPxWdth($utils->AutumnAsString()), $TABLE_HEIGHT, $utils->AutumnAsString(), 0, 0, 'L');
        $this->SetFont('ZapfDingBats', '', $this->DEFAULT_FONT_SIZE);
        $this->setY($cachedMidY);
        $this->setX($cachedX + $this->getStrPxWdth($utils->AutumnAsString()));
        $this->Cell($TABLE_HEIGHT / 2, $TABLE_HEIGHT / 2, !empty($this->emne->getTerminStatus() == 0 or $emne->getTerminStatus() == 2) ? '4' : '', 1, 0);
        $cachedX = $this->getX();
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Enkeltemne
        $this->setY($cachedY);
        $this->setX($cachedX + $TABLE_HEIGHT);
        $this->Cell($this->getStrPxWdth($utils->EnkeltemneAsString()), $TABLE_HEIGHT, $utils->EnkeltemneAsString(), 1, 0, 'L', true);
        $this->SetFont('ZapfDingBats', '', $this->DEFAULT_FONT_SIZE);
        $cachedX = $this->getX();
        $this->setY($cachedY + $TABLE_HEIGHT / 4);
        $this->setX($cachedX);
        $this->Cell($TABLE_HEIGHT / 2, $TABLE_HEIGHT / 2, !empty($emne->getEnkeltEmne() == 1) ? '    4' : '', 1, 0, 'C');
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Studiested
        $this->setY($this->postIncYValue($TABLE_HEIGHT + $EXTRA_HEIGHT / 2));
        $this->Cell($this->getStrPxWdth($utils->Studiested()), $TABLE_HEIGHT, $utils->Studiested(), 1, 0, 'L', true);
        $cachedX = $this->getX();
        $cachedY = $this->getY();
        $locationArray = $utils->getTwoDimensionalBooleanArrayForStudyLocation($emne->getUndervisningsSted());
        for ($i = 0; $i <= count($locationArray) - 1; $i++) {
            $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);
            $this->setY($cachedY);
            $this->setX($cachedX + $TABLE_HEIGHT / 2);
            $this->Cell($this->getStrPxWdth($locationArray[$i][0]) + $TABLE_HEIGHT, $TABLE_HEIGHT, $utils->handleISO($locationArray[$i][0]), 1, 0, 'L');
            $this->SetFont('ZapfDingBats', '', $this->DEFAULT_FONT_SIZE);
            $cachedX = $this->getX();
            $this->setY($cachedY + $TABLE_HEIGHT / 4);
            $this->setX($cachedX - $TABLE_HEIGHT);
            $this->Cell($TABLE_HEIGHT / 2, $TABLE_HEIGHT / 2, !empty($locationArray[$i][0 + 1] == '1') ? '   4' : '', 1, 0, 'C');
            $this->setX($this->getX() + $TABLE_HEIGHT / 2);
        }
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Nettstudent-tilbud
        $nettbasert = ($emne->getUndervisningsSted()->getNettbasert() == 1) ? true : false;
        $this->setY($this->postIncYValue($TABLE_HEIGHT));
        $word = $utils->TilbudForNettstudentAsString();
        $this->Cell($this->getStrPxWdth($word), $TABLE_HEIGHT, $word, 1, 0, 'L', true);
        if (!$nettbasert) {
            $this->setX($this->getX() - $this->getStrPxWdth($word));
            $this->Cell($this->getStrPxWdth($word), $TABLE_HEIGHT, str_repeat('-', strlen($word) * $this->STRIKETHROUGH_FACTOR), 0, 0, 'C');
        }
        $onlineOfferAray = $utils->getTwoDimensionalBooleanArrayForOnlineCourse($emne->getNettstudentTilbud());
        for ($i = 0; $i <= count($onlineOfferAray) - 1; $i++) {
            $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);
            $this->setY($this->postIncYValue($TABLE_HEIGHT));
            $this->setX($this->lMargin + $TABLE_HEIGHT);

            $word = $onlineOfferAray[$i][0];
            $this->Cell($this->getStrPxWdth($word), $TABLE_HEIGHT, $utils->handleISO($word), 1, 0, 'L', true);
            if (!$nettbasert) {
                $this->setX($this->getX() - $this->getStrPxWdth($word));
                $this->Cell($this->getStrPxWdth($word), $TABLE_HEIGHT, str_repeat('-', $this->getStrPxWdth($word) * $this->STRIKETHROUGH_FACTOR), 0, 0, 'L');
            }
            if ($i != count($onlineOfferAray) - 1) {
                $this->SetFont('ZapfDingBats', '', $this->DEFAULT_FONT_SIZE);
                $cachedX = $this->getX();
                $this->setX($cachedX);
                $this->Cell($TABLE_HEIGHT, $TABLE_HEIGHT, !empty($onlineOfferAray[$i][1] == '1') ? '   4' : '', 1, 0, 'C');
            } else {
                $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);
                $this->Cell(parent::GetPageWidth() - $this->rMargin * 4, $TABLE_HEIGHT, $onlineOfferAray[$i][1], 1, 0, 'L');
            }
        }

        // Søknadsfrist, Eksamensdato/type & undervisnings-/eksamensspråk
        $this->setY($this->postIncYValue($TABLE_HEIGHT + $EXTRA_HEIGHT));
        $this->Cell($this->getStrPxWdth($utils->FristerAsString()), $TABLE_HEIGHT, $utils->FristerAsString(), 1, 0, 'L', true);
        $this->setY($this->postIncYValue($TABLE_HEIGHT));
        $this->setX($this->getX() + $TABLE_HEIGHT);
        $this->Cell($this->getStrPxWdth($utils->SoknadsfirstAsString()), $TABLE_HEIGHT, $utils->SoknadsfirstAsString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($emne->getSoeknadsfrist()), $TABLE_HEIGHT, $emne->getSoeknadsfrist(), 1, 0, 'L');
        $this->setY($this->postIncYValue($TABLE_HEIGHT));
        $this->setX($this->getX() + $TABLE_HEIGHT);
        $this->Cell($this->getStrPxWdth($utils->EksamensDatoAsString()), $TABLE_HEIGHT, $utils->EksamensDatoAsString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($emne->getEksamensDato()), $TABLE_HEIGHT, $emne->getEksamensDato(), 1, 0, 'L');
        $this->setY($this->postIncYValue($TABLE_HEIGHT));
        $this->setX($this->getX() + $TABLE_HEIGHT);
        $this->Cell($this->getStrPxWdth($utils->EksamenstypeAsString()), $TABLE_HEIGHT, $utils->EksamenstypeAsString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($utils->EksamensTypeOfEmneAsString($emne->getEksamensType())), $TABLE_HEIGHT, $utils->EksamensTypeOfEmneAsString($emne->getEksamensType()), 1, 0, 'L');
        $this->setY($this->postIncYValue($TABLE_HEIGHT));
        $this->setX($this->getX() + $TABLE_HEIGHT);
        $this->Cell($this->getStrPxWdth($utils->UndervisningsSprakAsString()), $TABLE_HEIGHT, $utils->UndervisningsSprakAsString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($utils->UndervisningsSprakTypeAsString($emne->getUndervisningsspraak())), $TABLE_HEIGHT, $utils->UndervisningsSprakTypeAsString($emne->getUndervisningsspraak()), 1, 0, 'L');

        // New page
        $this->AddPage();

        // Forkunnskapskrav & Anbefalt forkunnskaper
        $maxYvalue = 0;
        $this->setY($this->yValUnderHeader);
        $this->yValue = 0;
        $cachedY = $this->getY();
        $this->Cell($this->getStrPxWdth($utils->ForkunnskapsKravAsString()), $TABLE_HEIGHT, $utils->ForkunnskapsKravAsString(), 1, 0, 'L', true);
        $forkunnskapsKravArray = $emne->getAllForkunnskapsKravAsStringArray();//= array('ABC-123', 'BBA-223', 'AJD-135', 'ASD-221'); // TODO: use '= $emne->getForkunnskapsKravEmneID()' when function in testQueries_Emne.php fixed
        for ($i = 0; $i < count($forkunnskapsKravArray); $i++) {
            $this->setY($this->getY() + $TABLE_HEIGHT);
            $this->setX($this->lMargin + $TABLE_HEIGHT);
            $this->Cell($this->getStrPxWdth($forkunnskapsKravArray[$i]), $TABLE_HEIGHT, $forkunnskapsKravArray[$i], 1, 0, 'L');
        }
        $this->setY($cachedY);
        $this->setX($this->getStrPxWdth($utils->ForkunnskapsKravAsString()) + $TABLE_HEIGHT * 6);
        $this->Cell($this->getStrPxWdth($utils->AnbefaltForkunnskapsKravAsString()), $TABLE_HEIGHT, $utils->AnbefaltForkunnskapsKravAsString(), 1, 0, 'L', true);
        $anbefaltForkunnskapsKravArray = $emne->getAllAnbefaltForkunnskapsKravAsStringArray();//= array('DDA-112', 'DSA-113', 'AMT-424', 'RPK-332', 'AAB-123', 'NNC-937');
        for ($i = 0; $i < count($anbefaltForkunnskapsKravArray); $i++) {
            $this->setY($this->getY() + $TABLE_HEIGHT);
            $this->setX($this->getStrPxWdth($utils->ForkunnskapsKravAsString()) + $TABLE_HEIGHT * 6 + $TABLE_HEIGHT);
            $this->Cell($this->getStrPxWdth($anbefaltForkunnskapsKravArray[$i]), $TABLE_HEIGHT, $anbefaltForkunnskapsKravArray[$i], 1, 0, 'L');
        }
        if (count($forkunnskapsKravArray) > count($anbefaltForkunnskapsKravArray))
            $this->setY($cachedY + count($forkunnskapsKravArray) * $TABLE_HEIGHT + $TABLE_HEIGHT);
        else
            $this->setY($cachedY + count($anbefaltForkunnskapsKravArray) * $TABLE_HEIGHT + $TABLE_HEIGHT);

        // Kunnskaper og forståelse
        $this->setY($this->getY() + $TABLE_HEIGHT / 2);
        $this->Cell($this->getStrPxWdth($utils->KunnskaperOgForstaelseAsString()), $TABLE_HEIGHT, $utils->KunnskaperOgForstaelseAsString(), 1, 0, 'L', true);
        $this->setY($this->getY() + $TABLE_HEIGHT);
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->SMALL_FONT_SIZE);
        $this->MultiCell(parent::GetPageWidth() - $this->rMargin * 2, 6, $utils->handleISO($emne->getLaeringsResultat()->getKunnskaperOgForstaaelse()), 1, 'L');
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Ferdigheter
        $this->setY($this->getY() + $TABLE_HEIGHT / 2);
        $this->Cell($this->getStrPxWdth($utils->FerdigheterAsString()), $TABLE_HEIGHT, $utils->FerdigheterAsString(), 1, 0, 'L', true);
        $this->setY($this->getY() + $TABLE_HEIGHT);
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->SMALL_FONT_SIZE);
        $this->MultiCell(parent::GetPageWidth() - $this->rMargin * 2, 6, $utils->handleISO($emne->getLaeringsResultat()->getFerdigheter()), 1, 'L');
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Kompetanse
        $this->setY($this->getY() + $TABLE_HEIGHT / 2);
        $this->Cell($this->getStrPxWdth($utils->KompetanseAsString()), $TABLE_HEIGHT, $utils->KompetanseAsString(), 1, 0, 'L', true);
        $this->setY($this->getY() + $TABLE_HEIGHT);
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->SMALL_FONT_SIZE);
        $this->MultiCell(parent::GetPageWidth() - $this->rMargin * 2, 6, $utils->handleISO($emne->getLaeringsResultat()->getKompetanse()), 1, 'L');
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Faglig innhold
        $this->setY($this->getY() + $TABLE_HEIGHT / 2);
        $this->Cell($this->getStrPxWdth($utils->FagligInnholdAsString()), $TABLE_HEIGHT, $utils->FagligInnholdAsString(), 1, 0, 'L', true);
        $this->setY($this->getY() + $TABLE_HEIGHT);
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->SMALL_FONT_SIZE);
        $this->MultiCell(parent::GetPageWidth() - $this->rMargin * 2, 6, $utils->handleISO($emne->getFagligInnhold()), 1, 'L');
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // New page
        $this->AddPage();
        $this->setY($this->yValUnderHeader);

        // Arbeidskrav, eksamen og vurdering
        $this->Cell($this->getStrPxWdth($utils->ArbeidsKravAsString()), $TABLE_HEIGHT, $utils->ArbeidsKravAsString(), 1, 0, 'L', true);
        $arbeidsKravBeskrivelseArray = $emne->getArbeidskrav_BeskrivelseAsStringArray();
        for ($i = 0; $i < count($arbeidsKravBeskrivelseArray); $i++) {
            $this->setY($this->getY() + $TABLE_HEIGHT);
            $this->setX($this->lMargin + $TABLE_HEIGHT);
            $this->SetFont($this->DEFAULT_FONT, 'B', $this->SMALL_FONT_SIZE);
            $this->MultiCell(parent::GetPageWidth() - $this->rMargin * 3, 6, $utils->handleISO($arbeidsKravBeskrivelseArray[$i]), 1, 'L');
            $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);
        }

        // Karakterskala
        $this->setY($this->getY() + $TABLE_HEIGHT);
        $this->Cell($this->getStrPxWdth($utils->KarakterskalaAsString()), $TABLE_HEIGHT, $utils->KarakterskalaAsString(), 1, 0, 'L', true);
        $this->Cell($this->getStrPxWdth($utils->KarakterTypeAsString($emne->getKarakterSkala())), $TABLE_HEIGHT, $utils->KarakterTypeAsString($emne->getKarakterSkala()), 1, 0, 'L');
        $this->setY($this->getY() + $TABLE_HEIGHT);

        // Emneansvarlige - Epost
        $this->setY($this->getY() + $TABLE_HEIGHT);
        $this->Cell($this->getStrPxWdth($utils->EmneAnsvarligeEpostAsString()), $TABLE_HEIGHT, $utils->EmneAnsvarligeEpostAsString(), 1, 0, 'L', true);
        $ansvarligeEpostArray = $emne->getAllAnsvarlig_Epost_AsStringArray();
        foreach ($ansvarligeEpostArray as $epost) {
            $this->setY($this->getY() + $TABLE_HEIGHT);
            $this->setX($this->lMargin + $TABLE_HEIGHT);
            $this->Cell($this->getStrPxWdth($epost), $TABLE_HEIGHT, $epost, 1, 0, 'L');
        }

        // Emnedeler
        $this->setY($this->getY() + $TABLE_HEIGHT + $EXTRA_HEIGHT);
        $this->Cell($this->getStrPxWdth($utils->EmneDelerAsString()), $TABLE_HEIGHT, $utils->EmneDelerAsString(), 1, 0, 'L', true);
        $emneDelerArray = $emne->getAllAnsvarlig_EmneDel_AsStringArray(true);
        foreach ($emneDelerArray as $emneDel) {
            $this->setY($this->getY() + $TABLE_HEIGHT);
            $this->setX($this->lMargin + $TABLE_HEIGHT);
            $this->Cell($this->getStrPxWdth($emneDel), $TABLE_HEIGHT, $emneDel, 1, 0, 'L');
        }

        // Statistikk
        $this->setY($this->getY() + $TABLE_HEIGHT + $EXTRA_HEIGHT);
        $str = $this->languageCode==2?'Number of edits: ':'Antall endringer: ';
        $str .= $emne->getEditCount();
        $this->Cell($this->getStrPxWdth($str), $TABLE_HEIGHT, $str, 1, 0, 'L');
        $this->setY($this->getY() + $TABLE_HEIGHT + $EXTRA_HEIGHT);
        $str = $this->languageCode==2?'Number of page-views for emne: ':'Antall visninger av emne: ';
        $str .= $emne->getHitCount();
        $this->Cell($this->getStrPxWdth($str), $TABLE_HEIGHT, $str, 1, 0, 'L');
    }

    // Page footer
    function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, $this->getPageNumberString() . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    /**
     * @return Emne
     */
    public function getEmne(): Emne
    {
        return $this->emne;
    }

    public function getTitle(): string
    {
        return $this->emne->getEmneKode() . " | " . $this->emne->getEmneNavn($this->languageCode);
    }

    public function getTimestamp(): string
    {
        return date_create('now')->format('Y-m-d H:i:s');
    }

    public function getExtractionTimeStampAsString(): string
    {
        return array(
                "Disse dataene ble uthentet: ",
                "Desse datane vart henta ut: ",
                "This data was retrieved: "
            )[$this->languageCode] . $this->getTimestamp();
    }

    public function getImageLogoHeightResizeFactor(): float
    {
        return getimagesize($this->LOGO_IMAGE_ASSET_PATH)[0] / $this->IMAGE_TITLE_SIZE + $this->IMAGE_TITLE_SIZE * 6.09;
    }

    public function getPageNumberString(): string
    {
        if ($this->languageCode == 0 or $this->languageCode == 1)
            return "Side ";
        else
            return "Page ";
    }

    public function postIncYValue(int $yValue)
    {
        $this->yValue += $yValue;
        return $this->yValue;
    }

    function getStrPxWdth($input)
    {
        return parent::getStrPxWdth($input) + 2;
    }
}