<?php
require_once 'base.php';

/**********************************************************************************
 * PDF_statistics - Extended from FPDF (http://www.fpdf.org/) and Sector from FPDF*
 *                                                                                *
 * Version: 1.0                                                                   *
 * Creation time:   2020-04-20                                                    *
 * Last change:     2020-04-21 23:00                                              *
 * Author:  Anders Rubach Ese                                                     *
 *********************************************************************************/
class PDF_statistics extends PDF
{
    var $legends;
    var $wLegend;
    var $sum;
    var $NbVal;
    var $curLineBrk = 0;
    var $title;

    public UtilsPDF $utilsPDF;

    // Fixed Variables
    private $TABLE_HEIGHT = 8;
    private $yValUnderHeader;

    // Variables
    protected $yValue = 0;

    /**
     * PDF constructor
     */
    public function __construct(int $languageCode)
    {
        // Override parent
        parent::__construct();
        parent::setLanguageCode($languageCode);
        parent::__constructOverload($languageCode, $this->getFileName($languageCode), $this->getTitle($languageCode));

    }

    /**
     * Set body-elements in PDF
     */
    public function setStatisticsBody()
    {
        global $languageCode;
        $lc = $languageCode;
        $this->title = ($lc==0?'Statistikk':'Statistics');
        $this->AddPage();

        global $database;
        global $TABLE_HEIGHT;

        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);

        // Generate pie-chart of UndervisningSsted
        $this->Ln(8);
        $this->columnBreak($this->appendLineBrk(), ($lc==0?'Undervisningssted':'Course locations'), 1);
        $this->Ln(8);
        $cachedY = $this->getY();
        $this->customTableBreak(($lc==0?'Totalt ':'In total ').$database->countEmne().($lc==0?' emner':' courses'), false, 2);
        $this->generatePieChart(array(
            array($database->countUndervisningsStedNarvik(),    'Narvik',     array(219, 168, 121)),
            array($database->countUndervisningsStedTromsoe(),   'Tromsø',     array(143, 219, 107)),
            array($database->countUndervisningsStedAlta(),      'Alta',       array(227, 210, 166)),
            array($database->countUndervisningsStedMoIRana(),   'Mo i Rana',  array(186, 122, 250)),
            array($database->countUndervisningsStedBodoe(),     'Bodø',       array(227, 166, 212)),
            array($database->countUndervisningsStedNettbasert(),($lc==0?'Nettbasert':'Online'), array(92, 187,  204))),
            85, 75, 25, 20, false, 2);
        $this->Ln(3);

        // Generate Bar Diagram for EnkeltEmne
        $this->generateBarDiagramLineBreak(array(
            ($lc==0?'Enkeltemne':'Single course')=>$database->countEmneGivenEnkeltEmne(1),
            ($lc==0?'Ikke enkeltemne':'not single')=>$database->countEmneGivenEnkeltEmne(0)),
            ($lc==0?'Enkeltemner':'Single courses'), $this->appendLineBrk() , 200, 25, 30, 1, null, 5);

        // Generate Bar Diagram for Nettbasert
        $this->generateBarDiagramLineBreak(array(
            ($lc==0?'Nettbasert':'Online')=>$database->countEmneGivenNettbasert(1),
            ($lc==0?'Ikke nettbasert':'Not online')=>$database->countEmneGivenNettbasert(0)),
            ($lc==0?'Nettbaserte emner':'Online courses'), $this->appendLineBrk() , 200, 25, 30, 1, null, 5);

        // Generate Bar Diagram for Arkivert
        $this->generateBarDiagramLineBreak(array(
            ($lc==0?'Arkivert':'Archived')=>$database->countEmneGivenArkivert(1),
            ($lc==0?'Ikke arkivert':'Not archived')=>$database->countEmneGivenArkivert(0)),
            ($lc==0?'Arkiverte emner':'Archived courses'), $this->appendLineBrk() , 200, 25, 30, 1, null, 5);

        // Generate Bar Diagram for Year
        $yearArray = array();
        for ($yr = date('Y'), $yrMax=$yr+5,$yr-=5; $yr <= $yrMax; $yr++)
            $yearArray[strval($yr)] = $database->countEmneGivenYear($yr);
        $this->generateBarDiagramLineBreak($yearArray, ($lc==0?('Emner i inneværende år +- 5 år'):('Courses in current year +- 5 years')),
            $this->appendLineBrk(), 200, 60, 30, 0, array(51, 204, 255), 5);
        $this->AddPage();

        // Generate Pie-Chart for TerminStatus, Undervisningsspraak and Status
        $this->columnBreak($this->appendLineBrk(), ($lc==0?'Termin for emner, undervisning-/eksamenspråk og status på godkjenning av emne':'Terms for courses and education-/examlanguage and approval status of courses'), 5);
        $this->appendY(12);
        $cachedY = $this->getY();
        $this->generatePieChart(array(
            array($database->countEmneGivenTerminStatus(0), ($lc==0?'Høst':'Autumn'),array(92, 187,  204)),
            array($database->countEmneGivenTerminStatus(1), ($lc==0?'Vår':'Spring'), array(245, 167, 66)),
            array($database->countEmneGivenTerminStatus(2), ($lc==0?'Begge':'Both'), array(182, 136, 219))),
            50, 55, 15, -20, false, 1);
        $this->setY($cachedY);
        $this->generatePieChart(array(
            array($database->countEmneGivenUndervisningsspraak(0), ($lc==0?'Norsk':'Norwegian'),array(92, 187,  204)),
            array($database->countEmneGivenUndervisningsspraak(1), ($lc==0?'Engelsk':'English'),array(182, 136, 219))),
            112, 55, 15, -20, false, 7);
        $this->setY($cachedY);
        $this->generatePieChart(array(
            array($database->countEmneGivenStatus(0), ($lc==0?'Under godkjenning':'Under approval'), array(182, 136, 219)),
            array($database->countEmneGivenStatus(1), ($lc==0?'Ikke godkjent':'Not approved'),       array(245, 167, 66)),
            array($database->countEmneGivenStatus(2), ($lc==0?'Godkjent':'Approved'),                array(92, 187,  204))),
            190, 55, 15, -20, false, 15);


        // Generate Pie-Chart for EksamensType and KarakterSkala
        $this->columnBreak($this->appendLineBrk(), ($lc==0?'Eksamenstyper og karakterskala':'Exam types and grading system'), 18);
        $this->appendY(12);
        $cachedY = $this->getY();
        $this->generatePieChart(array(
            array($database->countEmneGivenEksamensType(0), ($lc==0?'Muntlig':'Spoken'),    array(92, 187,  204)),
            array($database->countEmneGivenEksamensType(1), ($lc==0?'Praktisk':'Practical'),array(245, 167, 66)),
            array($database->countEmneGivenEksamensType(2), ($lc==0?'Skriftlig':'Written'), array(182, 136, 219))),
            65, 110, 15, 60, false, 2);
        $this->setY($cachedY);
        $this->generatePieChart(array(
            array($database->countEmneGivenKarakterSkala(0), 'A/F', array(182, 136, 219)),
            array($database->countEmneGivenKarakterSkala(0), ($lc==0?'Bestått/ikke':'pass/failed'),array(245, 167, 66))),
            140, 110, 15, 60, false, 10);
        $this->appendY(12);
        $cachedY = $this->getY();


        // Generate pie-chart of BrukerType
        $this->columnBreak($this->appendLineBrk(), ($lc==0?'Antall brukerrettigheter':'Number of user rights'), 1);
        $this->setYPos($cachedY + 10);
        $cachedY = $this->getY();
        $this->customTableBreak('Totalt '.$database->countBruker().' brukere',false, 1);
        $this->generatePieChart(array(
            array($database->countBrukerDelegerteRettigheter(),$this->userTypeToString(5), array(219, 168, 121)),
            array($database->countBrukerSystemAdmin(),         $this->userTypeToString(0), array(143, 219, 107)),
            array($database->countBrukerSuperuser(),           $this->userTypeToString(1), array(227, 210, 166)),
            array($database->countBrukerObservatoer(),         $this->userTypeToString(2), array(186, 122, 250)),
            array($database->countBrukerEmneansvarlig(),       $this->userTypeToString(3), array(227, 166, 212)),
            array($database->countBrukerInstituttleder(),      $this->userTypeToString(4), array(92, 187,  204))),
            84, 160, 30, 20, false, 1);
        $this->Ln(8);

        // Faculties - Emne and Bruker
        $facultyArray = array(
            array(-1, ($lc==0?'Fakultet for biovitenskap, fiskeri og økonomi':'Faculty of Life Sciences, Fisheries and Economics'),                         array(219, 168, 121)),
            array(-1, ($lc==0?'Fakultet for humaniora, samfunnsvitenskap og lærerutdanning':'Faculty of Humanities, Social Sciences and Teacher Education'),array(143, 219, 107)),
            array(-1, ($lc==0?'Fakultet for ingeniørvitenskap og teknologi':'Faculty of Engineering Science and Technology'),                               array(92, 187, 204)),
            array(-1, ($lc==0?'Fakultet for naturvitenskap og teknologi':'Faculty of Science and Technology'),                                              array(186, 122, 250)),
            array(-1, ($lc==0?'Det helsevitenskapelige fakultet':'The Faculty of Health Sciences'),                                                         array(227, 166, 212)),
            array(-1, ($lc==0?'Det juridiske fakultet':'The Faculty of Law'),                                                                               array(227, 210, 166)),
            array(-1, ($lc==0?'Norges arktiske universitetsmuseum og akademi for kunstfag':'Norwegian Arctic University Museum and Academy of Arts'),       array(133, 186, 222)),
            array(-1, ($lc==0?'Universitetsbiblioteket':'University Library'),                                                                              array(222, 142, 133)));
        for ($i=0, $arrSize=count($facultyArray);$i<$arrSize;$i++)
            $facultyArray[$i][1].=' '.$database->countEmneGivenFakultet($i).' / '.$database->countBrukerGivenFakultet($i);
        $this->customTableBreak(($lc==0?'Ant. emner / brukere per fakultet':'Number of courses / users per faculty'), false, 1);
        for ($i = 0,$flip=false; $i < $arrSize; $i++) {
            $facultyArray[$i][0] = !$flip?($database->countEmneGivenFakultet($i)):($database->countBrukerGivenFakultet($i));
            if ($i==$arrSize-1) {
                $this->generatePieChart($facultyArray, 145, !$flip?215:255, 15, 20, false, 1, !$flip, false); // set bool $int_val_text=false
                if (!$flip)$i=-1; // Flip value-output
                $flip=true;
            }
        }


        // TODO: Generate dates for archived courses in LineDiagram
    }

    private function generateBarDiagramLineBreak(array $contentArray, String $title, int $lineNum, int $width, int $height, int $yAppendage, int $newLnSize, $fillClr=null, $titleYOffset=true) {
        $this->columnBreak($lineNum, $title, $newLnSize);
        if ($titleYOffset!=null) $this->appendY($titleYOffset);
        $cachedX = $this->getX();
        $cachedY = $this->getY();
        $this->SetX(parent::getRMargin());
        $this->BarDiagram($width, $height, $contentArray, '%l : %v (%p)', $fillClr);
        $this->SetXY($cachedX, $cachedY + $yAppendage);
    }

    private function columnBreak(int $lineNum, String $title, int $newLnSize): void
    {
        $this->Ln($newLnSize);
        $this->SetFont('Arial', 'BIU', 12);
        $this->Cell(0, 5, $lineNum.' - '.$this->utilsPDF->handleISO($title));
        $this->SetFont($this->DEFAULT_FONT, 'B', $this->DEFAULT_FONT_SIZE);
    }

    private function userTypeToString(int $pos): String
    {
        return array(
            array('Systemadmin', 'Systemadmin', 'Systemadmin'),
            array('Superuser', 'Superuser', 'Superuser'),
            array('Observatør', 'Observatør', 'Observator'),
            array('Emneansvarlig', 'Emneansvarleg', 'Course coordinator'),
            array('Instituttleder', 'Instituttleiar', 'Head of Department'),
            array('Delegerte rettigheter', 'Delegerte rettar', 'Delegated rights')
        )[$pos][parent::getLanguageCode()];
    }

    /**
     * Prints a pie chart partially using the Sector script from FPDF
     * @param array|array[] $contentArray Assoc. 2D array with array in 2nd dim. e.g. [[..] [int_value|string|[R_colorValue|G_colorValue|B_colorValue]] [..][..]]
     * @param int $xPos x-position to draw chart
     * @param int $yPos y-position to draw chart
     * @param int $radius radius of circle
     * @param int $startAngleFromTop start position in degrees from top
     * @param bool $debug detailed output
     * @param int $xPosTables
     * @param bool $textOutput
     * @param bool $val_text_output
     */
    private function generatePieChart(array $contentArray, int $xPos, int $yPos, int $radius, int $startAngleFromTop, bool $debug, int $xPosTables, bool $textOutput=true, $val_text_output=true): void {
        $totCount = 0;
        for ($i = 0, $sz=count($contentArray); $i < $sz; $i++)
            $totCount+=$contentArray[$i][0];
        $factorize360 = 360 / $totCount;

        for ($i = 0, $init = $startAngleFromTop; $i < $sz; $i++) {
            $this->SetFillColor($contentArray[$i][2][0], $contentArray[$i][2][1], $contentArray[$i][2][2]);
            if ($debug)$this->defTableBreak('startAngle-'.$init.'°', true);
            $cacheInit = $init;
            $init += $contentArray[$i][0] * $factorize360;
            $this->Sector($xPos, $yPos, $radius, $cacheInit, $init);
            if ($textOutput) $this->customTableBreak($contentArray[$i][1].($val_text_output?' - '.$contentArray[$i][0]:'') . ($debug? 'diff='.($init-$cacheInit).'°' : ''), true, $xPosTables);
            if ($debug)$this->defTableBreak('endAngle-'.$init.'°', true);
        }
    }

    /**
     * Print left bordered content glued vertically
     */
    private function defTableBreak(String $string, bool $fill)
    {
        $this->Cell($this->getStrPxWidth($string), $this->TABLE_HEIGHT, parent::getUtilsPDF()->handleISO($string), 1, 0, 'L', $fill);
        $this->appendY($this->TABLE_HEIGHT);
    }

    /**
     * Print custom_xOffset bordered content glued vertically
     * @param int $xOffset 1-19, 10=center
     */
    private function customTableBreak(String $string, bool $fill, int $xOffset)
    {
        $rMargin = parent::getRMargin();
        $xPos = parent::GetPageWidth() / 2 - ($rMargin *1.5);
        if ($this->withinValue($xOffset, 1, 9))
            $xPos = $rMargin*$xOffset;
        elseif ($this->withinValue($xOffset, 11, 19))
            $xPos = $rMargin*$xOffset-($rMargin*2);
        $this->setX($xPos);
        $this->defTableBreak($string, $fill);
    }

    /**
     * Return true if $value between $min/$max
     */
    function withinValue($value, $min, $max)
    {
        return (in_array($value, range($min, $max)));
    }

    /**
     * Returns document-title
     */
    private function getTitle($languageCode): String
    {
        return ($languageCode==0?'Statistikk':'Statistics');
    }

    /**
     * Returns document-fileName
     */
    private function getFileName($languageCode): String
    {
        return $this->getTitle($languageCode) . ' ('.parent::getTimestamp().')';
    }

    /**
     * Return width of given $string
     * @param String $string
     * @return int
     */
    private function getStrPxWidth(String $string): int
    {
        return parent::getStrPxWitdh($string) + 2;
    }

    /**
     * Append current y-value
     * @param int $yValue
     */
    private function appendY(int $yValue): void
    {
        $this->setY($this->getY() + $yValue);
    }

    /**
     * Set current y-value
     * @param int $yPos
     */
    private function setYPos(int $yPos): void
    {
        $this->setY($yPos);
    }

    /**
     * Sets Sector in Pie-Chart, used from FPDF-library: http://www.fpdf.org/en/script/script19.php
     * @param $xc
     * @param $yc
     * @param $r
     * @param $a
     * @param $b
     * @param string $style
     * @param bool $cw
     * @param int $o
     */
    function Sector($xc, $yc, $r, $a, $b, $style='FD', $cw=true, $o=90)
    {
        $d0 = $a - $b;
        if($cw){
            $d = $b;
            $b = $o - $a;
            $a = $o - $d;
        }else{
            $b += $o;
            $a += $o;
        }
        while($a<0)
            $a += 360;
        while($a>360)
            $a -= 360;
        while($b<0)
            $b += 360;
        while($b>360)
            $b -= 360;
        if ($a > $b)
            $b += 360;
        $b = $b/360*2*M_PI;
        $a = $a/360*2*M_PI;
        $d = $b - $a;
        if ($d == 0 && $d0 != 0)
            $d = 2*M_PI;
        $k = $this->k;
        $hp = $this->h;
        if (sin($d/2))
            $MyArc = 4/3*(1-cos($d/2))/sin($d/2)*$r;
        else
            $MyArc = 0;
        //first put the center
        $this->_out(sprintf('%.2F %.2F m',($xc)*$k,($hp-$yc)*$k));
        //put the first point
        $this->_out(sprintf('%.2F %.2F l',($xc+$r*cos($a))*$k,(($hp-($yc-$r*sin($a)))*$k)));
        //draw the arc
        if ($d < M_PI/2){
            $this->_Arc($xc+$r*cos($a)+$MyArc*cos(M_PI/2+$a),
                $yc-$r*sin($a)-$MyArc*sin(M_PI/2+$a),
                $xc+$r*cos($b)+$MyArc*cos($b-M_PI/2),
                $yc-$r*sin($b)-$MyArc*sin($b-M_PI/2),
                $xc+$r*cos($b),
                $yc-$r*sin($b)
            );
        }else{
            $b = $a + $d/4;
            $MyArc = 4/3*(1-cos($d/8))/sin($d/8)*$r;
            $this->_Arc($xc+$r*cos($a)+$MyArc*cos(M_PI/2+$a),
                $yc-$r*sin($a)-$MyArc*sin(M_PI/2+$a),
                $xc+$r*cos($b)+$MyArc*cos($b-M_PI/2),
                $yc-$r*sin($b)-$MyArc*sin($b-M_PI/2),
                $xc+$r*cos($b),
                $yc-$r*sin($b)
            );
            $a = $b;
            $b = $a + $d/4;
            $this->_Arc($xc+$r*cos($a)+$MyArc*cos(M_PI/2+$a),
                $yc-$r*sin($a)-$MyArc*sin(M_PI/2+$a),
                $xc+$r*cos($b)+$MyArc*cos($b-M_PI/2),
                $yc-$r*sin($b)-$MyArc*sin($b-M_PI/2),
                $xc+$r*cos($b),
                $yc-$r*sin($b)
            );
            $a = $b;
            $b = $a + $d/4;
            $this->_Arc($xc+$r*cos($a)+$MyArc*cos(M_PI/2+$a),
                $yc-$r*sin($a)-$MyArc*sin(M_PI/2+$a),
                $xc+$r*cos($b)+$MyArc*cos($b-M_PI/2),
                $yc-$r*sin($b)-$MyArc*sin($b-M_PI/2),
                $xc+$r*cos($b),
                $yc-$r*sin($b)
            );
            $a = $b;
            $b = $a + $d/4;
            $this->_Arc($xc+$r*cos($a)+$MyArc*cos(M_PI/2+$a),
                $yc-$r*sin($a)-$MyArc*sin(M_PI/2+$a),
                $xc+$r*cos($b)+$MyArc*cos($b-M_PI/2),
                $yc-$r*sin($b)-$MyArc*sin($b-M_PI/2),
                $xc+$r*cos($b),
                $yc-$r*sin($b)
            );
        }
        //terminate drawing
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='b';
        else
            $op='s';
        $this->_out($op);
    }

    /**
     * Used in Sector(..), sets Arc in Pie-Chart, used from FDPF-library: http://www.fpdf.org/en/script/script19.php
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     * @param $x3
     * @param $y3
     */
    function _Arc($x1, $y1, $x2, $y2, $x3, $y3 )
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1*$this->k,
            ($h-$y1)*$this->k,
            $x2*$this->k,
            ($h-$y2)*$this->k,
            $x3*$this->k,
            ($h-$y3)*$this->k));
    }

    function BarDiagram($w, $h, $data, $format, $color=null, $maxVal=0, $nbDiv=4)
    {
        $this->SetFont('Courier', '', 10);
        $this->SetLegends($data,$format);

        $XPage = $this->GetX();
        $YPage = $this->GetY();
        $margin = 2;
        $YDiag = $YPage + $margin;
        $hDiag = floor($h - $margin * 2);
        $XDiag = $XPage + $margin * 2 + $this->wLegend;
        $lDiag = floor($w - $margin * 3 - $this->wLegend);
        if($color == null)
            $color=array(155,155,155);
        if ($maxVal == 0) {
            $maxVal = max($data);
        }
        $valIndRepere = ceil($maxVal / $nbDiv);
        $maxVal = $valIndRepere * $nbDiv;
        $lRepere = floor($lDiag / $nbDiv);
        $lDiag = $lRepere * $nbDiv;
        $unit = $lDiag / $maxVal;
        $hBar = floor($hDiag / ($this->NbVal + 1));
        $hDiag = $hBar * ($this->NbVal + 1);
        $eBaton = floor($hBar * 80 / 100);

        $this->SetLineWidth(0.2);
        $this->Rect($XDiag, $YDiag, $lDiag, $hDiag);

        $this->SetFont('Courier', '', 10);
        $this->SetFillColor($color[0],$color[1],$color[2]);
        $i=0;
        foreach($data as $val) {
            //Bar
            $xval = $XDiag;
            $lval = (int)($val * $unit);
            $yval = $YDiag + ($i + 1) * $hBar - $eBaton / 2;
            $hval = $eBaton;
            $this->Rect($xval, $yval, $lval, $hval, 'DF');
            //Legend
            $this->SetXY(0, $yval);
            $this->Cell($xval - $margin, $hval, $this->legends[$i],0,0,'R');
            $i++;
        }

        //Scales
        for ($i = 0; $i <= $nbDiv; $i++) {
            $xpos = $XDiag + $lRepere * $i;
            $this->Line($xpos, $YDiag, $xpos, $YDiag + $hDiag);
            $val = $i * $valIndRepere;
            $xpos = $XDiag + $lRepere * $i - $this->getStrPxWidth($val) / 2;
            $ypos = $YDiag + $hDiag - $margin;
            $this->Text($xpos, $ypos, $val);
        }
    }

    function SetLegends($data, $format)
    {
        $this->legends=array();
        $this->wLegend=0;
        $this->sum=array_sum($data);
        $this->NbVal=count($data);
        foreach($data as $l=>$val)
        {
            $p=sprintf('%.2f',$val/$this->sum*100).'%';
            $legend=str_replace(array('%l','%v','%p'),array($l,$val,$p),$format);
            $this->legends[]=$legend;
            $this->wLegend=max($this->getStrPxWidth($legend),$this->wLegend);
        }
    }

    function appendLineBrk(): int
    {
        return ++$this->curLineBrk;
    }

    /**
     * Generates given LineGraphs from FPDF library: http://www.fpdf.org/en/script/script98.php
     * @param $w
     * @param $h
     * @param $data
     * @param string $options
     * @param null $colors
     * @param int $maxVal
     * @param int $nbDiv
     */
    function LineGraph($w, $h, $data, $options='', $colors=null, $maxVal=0, $nbDiv=4){
        /*******************************************
        Explain the variables:
        $w = the width of the diagram
        $h = the height of the diagram
        $data = the data for the diagram in the form of a multidimensional array
        $options = the possible formatting options which include:
        'V' = Print Vertical Divider lines
        'H' = Print Horizontal Divider Lines
        'kB' = Print bounding box around the Key (legend)
        'vB' = Print bounding box around the values under the graph
        'gB' = Print bounding box around the graph
        'dB' = Print bounding box around the entire diagram
        $colors = A multidimensional array containing RGB values
        $maxVal = The Maximum Value for the graph vertically
        $nbDiv = The number of vertical Divisions
         *******************************************/
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.2);
        $keys = array_keys($data);
        $ordinateWidth = 10;
        $w -= $ordinateWidth;
        $valX = $this->getX()+$ordinateWidth;
        $valY = $this->getY();
        $margin = 1;
        $titleH = 8;
        $titleW = $w;
        $lineh = 5;
        $keyH = count($data)*$lineh;
        $keyW = $w/5;
        $graphValH = 5;
        $graphValW = $w-$keyW-3*$margin;
        $graphH = $h-(3*$margin)-$graphValH;
        $graphW = $w-(2*$margin)-($keyW+$margin);
        $graphX = $valX+$margin;
        $graphY = $valY+$margin;
        $graphValX = $valX+$margin;
        $graphValY = $valY+2*$margin+$graphH;
        $keyX = $valX+(2*$margin)+$graphW;
        $keyY = $valY+$margin+.5*($h-(2*$margin))-.5*($keyH);
        //draw graph frame border
        if(strstr($options,'gB')){
            $this->Rect($valX,$valY,$w,$h);
        }
        //draw graph diagram border
        if(strstr($options,'dB')){
            $this->Rect($valX+$margin,$valY+$margin,$graphW,$graphH);
        }
        //draw key legend border
        if(strstr($options,'kB')){
            $this->Rect($keyX,$keyY,$keyW,$keyH);
        }
        //draw graph value box
        if(strstr($options,'vB')){
            $this->Rect($graphValX,$graphValY,$graphValW,$graphValH);
        }
        //define colors
        if($colors===null){
            $safeColors = array(0,51,102,153,204,225);
            for($i=0;$i<count($data);$i++){
                $colors[$keys[$i]] = array($safeColors[array_rand($safeColors)],$safeColors[array_rand($safeColors)],$safeColors[array_rand($safeColors)]);
            }
        }
        //form an array with all data values from the multi-demensional $data array
        $ValArray = array();
        foreach($data as $key => $value){
            foreach($data[$key] as $val){
                $ValArray[]=$val;
            }
        }
        //define max value
        if($maxVal<ceil(max($ValArray))){
            $maxVal = ceil(max($ValArray));
        }
        //draw horizontal lines
        $vertDivH = $graphH/$nbDiv;
        if(strstr($options,'H')){
            for($i=0;$i<=$nbDiv;$i++){
                if($i<$nbDiv){
                    $this->Line($graphX,$graphY+$i*$vertDivH,$graphX+$graphW,$graphY+$i*$vertDivH);
                } else{
                    $this->Line($graphX,$graphY+$graphH,$graphX+$graphW,$graphY+$graphH);
                }
            }
        }
        //draw vertical lines
        $horiDivW = floor($graphW/(count($data[$keys[0]])-1));
        if(strstr($options,'V')){
            for($i=0;$i<=(count($data[$keys[0]])-1);$i++){
                if($i<(count($data[$keys[0]])-1)){
                    $this->Line($graphX+$i*$horiDivW,$graphY,$graphX+$i*$horiDivW,$graphY+$graphH);
                } else {
                    $this->Line($graphX+$graphW,$graphY,$graphX+$graphW,$graphY+$graphH);
                }
            }
        }
        //draw graph lines
        foreach($data as $key => $value){
            $this->setDrawColor($colors[$key][0],$colors[$key][1],$colors[$key][2]);
            $this->SetLineWidth(0.8);
            $valueKeys = array_keys($value);
            for($i=0;$i<count($value);$i++){
                if($i==count($value)-2){
                    $this->Line(
                        $graphX+($i*$horiDivW),
                        $graphY+$graphH-($value[$valueKeys[$i]]/$maxVal*$graphH),
                        $graphX+$graphW,
                        $graphY+$graphH-($value[$valueKeys[$i+1]]/$maxVal*$graphH)
                    );
                } else if($i<(count($value)-1)) {
                    $this->Line(
                        $graphX+($i*$horiDivW),
                        $graphY+$graphH-($value[$valueKeys[$i]]/$maxVal*$graphH),
                        $graphX+($i+1)*$horiDivW,
                        $graphY+$graphH-($value[$valueKeys[$i+1]]/$maxVal*$graphH)
                    );
                }
            }
            //Set the Key (legend)
            $this->SetFont('Courier','',10);
            if(!isset($n))$n=0;
            $this->Line($keyX+1,$keyY+$lineh/2+$n*$lineh,$keyX+8,$keyY+$lineh/2+$n*$lineh);
            $this->SetXY($keyX+8,$keyY+$n*$lineh);
            $this->Cell($keyW,$lineh,$key,0,1,'L');
            $n++;
        }
        //print the abscissa values
        foreach($valueKeys as $key => $value){
            if($key==0){
                $this->SetXY($graphValX,$graphValY);
                $this->Cell(30,$lineh,$value,0,0,'L');
            } else if($key==count($valueKeys)-1){
                $this->SetXY($graphValX+$graphValW-30,$graphValY);
                $this->Cell(30,$lineh,$value,0,0,'R');
            } else {
                $this->SetXY($graphValX+$key*$horiDivW-15,$graphValY);
                $this->Cell(30,$lineh,$value,0,0,'C');
            }
        }
        //print the ordinate values
        for($i=0;$i<=$nbDiv;$i++){
            $this->SetXY($graphValX-10,$graphY+($nbDiv-$i)*$vertDivH-3);
            $this->Cell(8,6,sprintf('%.1f',$maxVal/$nbDiv*$i),0,0,'R');
        }
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(0.2);
    }
}