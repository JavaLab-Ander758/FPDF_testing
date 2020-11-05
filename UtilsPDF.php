<?php
require_once 'base.php';

class UtilsPDF
{
    public static $LOGO_IMAGE_ASSET_PATH =  'classes/PDF/fpdf182/assets_pdf/Uit_Logo_Bok_Sort_Minimized.jpg';

    private $languageCode;

    /**
     * UtilsPDF constructor.
     * @param $languageCode
     */
    public function __construct($languageCode)
    {
        $this->languageCode = $languageCode;
    }



    ///////////////////
    /// Translation ///
    ///////////////////
    function EmneKodeAsString()
    {
        if ($this->languageCode == 0 or $this->languageCode == 1)
            return 'Emnekode: ';
        else
            return 'Course code: ';
    }
    function EmneNivaAsString()
    {
        if ($this->languageCode == 0 or $this->languageCode == 1)
            return iconv('UTF-8', 'ISO-8859-1', 'Emnenivå: ');
        else
            return 'Level of course: ';
    }
    function EmneNavnNorwegianToString()
    {
        if ($this->languageCode == 0)
            return iconv('UTF-8', 'ISO-8859-1', 'Emnenavn bokmål: ');
        elseif ($this->languageCode == 1)
            return iconv('UTF-8', 'ISO-8859-1', 'Emnenamn bokmål: ');
        else
            return 'Course name Norwegian: ';
    }
    function EmneNavnNynorskToString()
    {
        if ($this->languageCode == 0)
            return iconv('UTF-8', 'ISO-8859-1', 'Emnenavn nynorsk: ');
        elseif ($this->languageCode == 1)
            return 'Emnenamn nynorsk';
        else
            return 'Course name nynorsk: ';
    }
    function EmneNavnEnglishToString()
    {
        if ($this->languageCode == 0)
            return iconv('UTF-8', 'ISO-8859-1', 'Emnenavn engelsk: ');
        elseif ($this->languageCode == 1)
            return 'Emnenamn engelsk: ';
        else
            return 'Course name english: ';
    }
    function BeskrivelseToString()
    {
        if ($this->languageCode == 0)
            return 'Beskrivelse: ';
        elseif ($this->languageCode == 1)
            return 'Skildring: ';
        else
            return 'Description: ';
    }
    function SemesterAsString()
    {
        if ($this->languageCode == 0 or $this->languageCode == 1)
            return iconv('UTF-8', 'ISO-8859-1', 'Semesterår: ');
        else
            return 'Semester year: ';
    }
    function LaeringOgAktiviteterAsString()
    {
        if ($this->languageCode == 0)
            return iconv('UTF-8', 'ISO-8859-1', 'Læringsformer og aktiviteter: ');
        elseif ($this->languageCode == 1)
            return 'Læringsformer og aktivitetar: ';
        else
            return 'Learning styles and activities: ';
    }
    function TerminAsString()
    {
        if ($this->languageCode == 0 or $this->languageCode == 1)
            return 'Undervisningstermin: ';
        else
            return 'Education season: ';
    }
    function VaarAsString()
    {
        if ($this->languageCode == 0 or $this->languageCode == 1)
            return $this->handleISO('Vår: ');
        else
            return 'Spring: ';
    }
    function AutumnAsString()
    {
        if ($this->languageCode == 0)
            return $this->handleISO('Høst: ');
        elseif ($this->languageCode == 1)
            return 'Haust: ';
        else
            return 'Autumn: ';
    }
    function EnkeltemneAsString()
    {
        if ($this->languageCode == 0 or $this->languageCode == 1)
            return 'Enkeltemne: ';
        else
            return 'Single subject: ';
    }
    function Studiested()
    {
        if ($this->languageCode == 0)
            return 'Studiested: ';
        elseif ($this->languageCode == 1)
            return 'Studiestad: ';
        else
            return 'Place of study: ';
    }
    function getTwoDimensionalBooleanArrayForStudyLocation(UndervisningsSted $undervisningsSted): array
    {
        $nettbasertString = $this->languageCode == 0 ? 'Nettbasert' : 'Online';
        return array(
            array('Narvik', $undervisningsSted->getNarvik()),
            array('Tromsø', $undervisningsSted->getTromsoe()),
            array('Alta', $undervisningsSted->getAlta()),
            array('Mo I Rana', $undervisningsSted->getMoIRana()),
            array('Bodø', $undervisningsSted->getBodoe()),
            array($nettbasertString, $undervisningsSted->getNettbasert())
        );
    }
    function getTwoDimensionalBooleanArrayForOnlineCourse(NettstudentTilbud $nettstudentTilbud): array
    {
//        $nettTilbudString = $this->languageCode == 0 ? 'Tilbud for nettstudenter' : 'Offers for online students';
        $transArray = array(
            array('Strømming av forelesninger', 'Strømming av forelesninger', 'Streaming lectures'),
            array('Åpent nettmøte under forelesningene', 'Ope nettmøte under forelesningene', 'Open online meeting during the lectures'),
            array('Nettmøte med studentassistent på kveldstid', 'Nettmøte med studentassistent på kveldstid', 'Online meeting with student assistant in the evening'),
            array('Oppfølgning via telefon, epost og/eller Skype', 'Oppfølgning via telefon, epost og/eller Skype', 'Follow-up via telephone, email and / or Skype'),
            array('Organisert opplegg i samlingsukene (lab, eskursjon, felt, etc.)', 'Organisert opplegg i samlingsvekene (lab, eskursjon, felt, etc.)', 'Organized schedule in the collection weeks (lab, excursion, field, etc.)'),
            array('Annet', 'Anna', 'Others')
        );

        $lc = $this->languageCode;
        return array(
            array($transArray[0][$lc], $nettstudentTilbud->getStroemming()),
            array($transArray[1][$lc], $nettstudentTilbud->getAapentNettmoete()),
            array($transArray[2][$lc], $nettstudentTilbud->getNettmoeteStudentassistentKveld()),
            array($transArray[3][$lc], $nettstudentTilbud->getOppfoelgingMedier()),
            array($transArray[4][$lc], $nettstudentTilbud->getOrganisertOpplegg()),
            array($transArray[5][$lc], $nettstudentTilbud->getAnnet())
        );
    }
    function FristerAsString()
    {
        if ($this->languageCode == 0)
            return 'Frister og eksamenstyper: ';
        elseif ($this->languageCode == 1)
            return 'Fristar og eksamenstypar: ';
        else
            return 'Deadlines and exam type: ';
    }
    function SoknadsfirstAsString() {
        return !empty($this->languageCode == 0 or 1) ? $this->handleISO('Søknadsfrist: ') : 'Application deadline: ';
    }
    function EksamensDatoAsString() {
        return !empty($this->languageCode == 0 or 1) ? 'Eksamensdato: ' : 'Exam date: ';
    }
    function EksamenstypeAsString() {
        return !empty($this->languageCode == 0 or 1) ? 'Eksamenstype: ' : 'Exam type';
    }
    function EksamensTypeOfEmneAsString(int $examType): String {
        return array(
            array('Muntlig', 'Praktisk', 'Skriftlig'),
            array('Munnleg', 'Praktisk', 'Skriftleg'),
            array('Spoken', 'Practical', 'Written')
        )[$this->languageCode][$examType];
    }
    function UndervisningsSprakAsString() {
        return !empty($this->languageCode == 0 or $this->languageCode == 1) ? $this->handleISO('Undervisning-/eksamensspråk: ') : 'Teaching-/exam language: ';
    }
    function TilbudForNettstudentAsString() {
        if ($this->languageCode == 0)
            return 'Tilbud for nettstudenter: ';
        elseif ($this->languageCode == 1)
            return 'Tilbud for nettstudentar: ';
        else
            return 'Offers for online students';
    }
    function UndervisningsSprakTypeAsString(int $undervisningType): String {
        return array(
            array('Norsk', 'Norwegian'),
            array('Norsk', 'Norwegian'),
            array('Norwegian', 'English')
        )[$this->languageCode][$undervisningType];
    }
    function ForkunnskapsKravAsString() {
        if ($this->languageCode == 0)
            return 'Krevde emner: ';
        elseif ($this->languageCode == 1)
            return 'Kravde emnar: ';
        else
            return 'Required courses: ';
    }
    function AnbefaltForkunnskapsKravAsString() {
        if ($this->languageCode == 0)
            return 'Anbefalte emner: ';
        elseif ($this->languageCode == 1)
            return $this->handleISO('Tilrådde emnar:');
        else
            return 'Recommended courses: ';

    }
    function KunnskaperOgForstaelseAsString() {
        if ($this->languageCode == 0)
            return $this->handleISO('Kunnskaper og forståelse:');
        elseif ($this->languageCode == 1)
            return $this->handleISO('kunnskapar og forståing:');
        else
            return 'Knowledge and understanding:';
    }
    function FerdigheterAsString() {
        if ($this->languageCode == 0)
            return $this->handleISO('Ferdigheter du lærer');
        elseif ($this->languageCode == 1)
            return $this->handleISO('Evner du lærer:');
        else
            return 'Skills you teach';
    }
    function KompetanseAsString() {
        return ($this->languageCode == 0 or $this->languageCode == 1) ? 'Kompetanse: ' : 'Competence: ';
    }
    function FagligInnholdAsString() {
        if ($this->languageCode == 0)
            return 'Faglig innhold: ';
        elseif ($this->languageCode == 1)
            return 'Fagler innhald: ';
        else
            return 'Academic content: ';
    }
    function ArbeidsKravAsString() {
        return ($this->languageCode == 0 or $this->languageCode == 1) ? 'Arbeidskrav, eksamen og vurdering: ' : 'Work requirements, exam and assessment: ';
    }
    function KarakterskalaAsString() {
        return ($this->languageCode == 0 or $this->languageCode == 1) ? 'Karakterskala: ' : 'Grading: ';
    }
    function KarakterTypeAsString(int $karakterType) {
        return ($karakterType == 0) ? 'A/F' : array($this->handleISO('Bestått-/ikke bestått'), $this->handleISO('Bestått-/ikkje bestått'), 'Pass/failed')[$this->languageCode];
    }
    function EmneAnsvarligeEpostAsString() {
        return ($this->languageCode == 0 or $this->languageCode == 1) ? 'Emneansvarlige - Epost: ' : 'Responsible for courses - Email';
    }
    function EmneDelerAsString() {
        if ($this->languageCode == 0)
            return 'Emnedeler: ';
        elseif ($this->languageCode == 1)
            return 'Emnedelar: ';
        else
            return 'Topic parts: ';
    }

    ///////////////
    /// General ///
    ///////////////
    /**
     * Returns input as 'ISO-8859-1'
     */
    function handleISO(String $input): String
    {
        return iconv('UTF-8', 'ISO-8859-1', $input);
    }


}