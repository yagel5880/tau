<?php 
// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {
	
	private $getFooterText = '';

	public function __construct($getFooterText) {
		parent::__construct();

		$this->getFooterText = $getFooterText;
	}

    //Page header
    public function Header() {

        // Logo
		if( isset($this->getHeaderLogo) && !empty($this->getHeaderLogo)) {
        	$image_file = $this->getHeaderLogo;
       		$this->Image($image_file, 5, 5, 40, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		}

		// Set font
		$this->SetFont('freesans', '', 12);

		$this->SetY(10);
		$this->SetX(0);

		$this->Cell(0, 10, 'עמוד '.trim($this->getAliasNumPage()).' מתוך '.trim($this->getAliasNbPages()), 0, false, 'C', 0, '', true, false, 'A', 'M');
    }

    // Page footer
    public function Footer() {
        
		$this->SetY(-15);
        $this->SetFont('freesans', '', 10);
       
		// Page number
       // $this->Cell(0, 10, $this->getFooterText(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	   $this->WriteHTML($this->getFooterText(), true, 0, true, 0);
    }

	// Set Header Logo
	public function setHeaderLogo($path='') {
		if(!is_string($path) && !file_exists($path)) $path = '';
		$this->getHeaderLogo = $path;
	}

	// Get Header logo path
	public function getHeaderLogo() {
		return $this->getHeaderLogo;
	}

	// Set footer text
	public function setFooterText($str) {
		if(!is_string($str)) $str = '';
		$this->getFooterText = $str;
	}

	// Get footer text info
	public function getFooterText() {
		return $this->getFooterText;
	}

	// Set tags data
	public function setApiDate($data) {
		if(is_object($data) || is_array($data)) $this->getApiDate = json_decode(json_encode($data));
		else throw new Exception('No data is set');
	}

	// Get tags data
	public function getApiDate(){
		if(!isset($this->getApiDate)) throw new Exception('No data is set');
		return $this->getApiDate;
	}


	// convert to gregorian year
	public function getGregorianYear($hebrew) {
		$hebrew2number = array( 'א' => 1, 'ב' => 2, 'ג' => 3, 'ד' => 4, 'ה' => 5, 'ו' => 6, 'ז' => 7, 'ח' => 8, 'ט' => 9, 'י' => 10, 'ך' => 20, 'כ' => 20, 'ל' => 30, 'ם' => 40, 'מ' => 40, 'ן' => 50, 'נ' => 50, 'ס' => 60, 'ע' => 70, 'ף' => 80, 'פ' => 80, 'ץ' => 90, 'צ' => 90, 'ק' => 100, 'ר' => 200, 'ש' => 300, 'ת' => 400 );

		$sum = 0;
		$otiot = preg_split('//u',$hebrew, -1, PREG_SPLIT_NO_EMPTY);
		
		for ($i=0; $i < count($otiot); ++$i ) {
			if ($val = @$hebrew2number[$otiot[$i]])
				$sum += $val;
		}

		$jd = jewishtojd(1 , 1 , 5000 + $sum);
		return $this->getGregorianYear = cal_from_jd($jd , CAL_GREGORIAN)['year'];
	}


}	
?>