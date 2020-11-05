<?php


abstract class PDF extends FPDF
{
    private static $LOGO_IMAGE_ASSET_PATH =  'classes/PDF/fpdf182/assets_pdf/Uit_Logo_Bok_Sort_Minimized.jpg';
    private int $IMAGE_TITLE_SIZE = 40;
    private static string $author = 'runtime-terror';

    private $languageCode;
    protected UtilsPDF $utilsPDF;
    private String $fileName;

    // Fixed Variables
    public $DEFAULT_FONT = 'Arial';
    public $LARGE_FONT_SIZE = 16;
    public $DEFAULT_FONT_SIZE = 10;
    public $SMALL_FONT_SIZE = 9;

    // Colors
    private $LIGHT_GREY = 242;
    private $DARK_GREY = array(217, 217, 217);
    private $MONGO_RED = array(255, 87, 87);
    private $PLAIN_WHITE = array(255, 255, 255);
    private $DISCO_BLUE = array(68, 192, 226);


    public function __constructOverload($languageCode, $fileName, $title)
    {
        parent::__construct('P', 'mm', 'A4');
        $this->AliasNbPages();
        $this->SetAuthor(self::$author);
        $this->SetTitle($fileName.'.pdf', false);
        $this->SetAuthor(self::$author);

        $this->languageCode = $languageCode;
        $this->fileName = $fileName;
        $this->utilsPDF = new UtilsPDF($languageCode);

//        $this->Output('', $this->fileName.'.pdf', false);
    }




    public function Header()
    {
        // Logo
        $this->Image(self::$LOGO_IMAGE_ASSET_PATH, 1, 10, $this->IMAGE_TITLE_SIZE);
        $this->SetFont('Arial', 'B', 10);

        // Move to the right
        $this->Cell(40);

        // Title
//        $cachedY = $this->postIncYValue($this->GetPageHeight() - $this->getImageLogoHeightResizeFactor());
        $this->setY(15);
        $this->setX(50);
        parent::SetFillColor($this->DARK_GREY[0], $this->DARK_GREY[1], $this->DARK_GREY[2]);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(140, 10, $this->fileName, 1, 0, 'C', true);
        $this->SetFont('Arial', 'B', 10);


        // Timestamp
        $this->setX(0);
        $this->setY(5);
        $this->Cell(0, 0, $this->getTimestampFormattedString());

        // Line break
        $this->Ln(20);
    }

    public function Footer()
    {
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial', 'I', 8);
        // Page number
        $this->Cell(0, 10, $this->languageCode == 0 ? 'Side: ' : 'Page: ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }


    /**
     * Returns formatted timestamp Y-m-d H:i:S
     */
    function getTimestamp(): String
    {
        return date_create('now')->format('Y-m-d H:i:s');
    }

    /**
     * Returns detailed timestamp for extraction time
     */
    function getTimestampFormattedString(): String
    {
        return array(
                "Disse dataene ble uthentet: ",
                "Desse datane vart henta ut: ",
                "This data was retrieved: "
            )[$this->getLanguageCode()] . $this->getTimestamp();
    }

//    public function postIncYValue(int $yValue)
//    {
//        $this->yValue += $yValue;
//        return $this->yValue;
//    }

//    public function getImageLogoHeightResizeFactor(): float
//    {
//        return getimagesize(self::$LOGO_IMAGE_ASSET_PATH)[0] / $this->IMAGE_TITLE_SIZE + $this->IMAGE_TITLE_SIZE * 6.09;
//    }

    /**
     * Returns current languageCode [0=NO,1=NN,2=EN]
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * Sets new languageCode [0=NO,1=NN,2=EN]
     */
    public function setLanguageCode($languageCodes): void
    {
        $this->languageCode = $languageCodes;
    }

    public function getUtilsPDF(): UtilsPDF
    {
        return $this->utilsPDF;
    }

    public function getStrPxWitdh($string) {
        return $this->getStrPxWdth($string) + 2;
    }

    public function printPDF()
    {
        $this->Output('', $this->fileName.'.pdf', false);
        ob_end_clean();
    }

    public function getRMargin(): int
    {
        return $this->rMargin;
    }
}