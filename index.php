<?php 
	// Include the main TCPDF library.
	require_once('vendor/TCPDF/tcpdf.php');	
	// Include custom class.
	require_once('class/MYPDF.php');
	
	//Reset Values
	$url = 'api/data.json';
	$mainTOCrgbColor = [0,0,0];
	$fileName = 'tau_'.time().'.pdf';
	$lang = 'Heb';
	$output = 'I';
	
	// Create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->setRTL(true);
	$pdf->SetFont('freesans', '', 12);
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('TAU');
	$pdf->SetTitle('Heading');
	$pdf->setFontSubsetting(true);
	$pdf->SetDefaultMonospacedFont('freesans');
	$pdf->SetMargins(20, 30, 20, 20);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);	
	$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
	
	// Set api data
	$pdf->setApiDate(json_decode(file_get_contents($url),1));

	if($pdf->getApiDate()){
		
		$pdf->setHeaderLogo($_SERVER['DOCUMENT_ROOT'].'pdf/img/tauLogo.jpg');
		
		// Doc lang
		if($pdf->getApiDate()->data->lang != 'Heb') {
			$lang = $pdf->getApiDate()->data->lang;
			$pdf->setRTL(false);
		}
		
		// footer text data
		$tclongkey = $pdf->getApiDate()->data->tclongkey;	
		$teurtoar = $pdf->getApiDate()->data->teurtoar;
		$teurshana = $pdf->getApiDate()->data->teurshana;
	
		$pdf->setFooterText('<div style="text-align: center;"><span dir="rtl">'.$teurtoar.'</span> | <span>'.$tclongkey.'</span> | <span>'.$teurshana.'</span></div>');
		
		// file name
		$year = $pdf->getGregorianYear($teurshana);
		$fileName =  $tclongkey.'_'.$year.'_'.$lang.'_'.date('Ymd').'.pdf';
		
		foreach ($pdf->getApiDate()->tabLinks as $tab) {

			$pdf->AddPage();
			$pdf->Bookmark($tab->label, 0, 0, '', 'B', $mainTOCrgbColor);
			
			$html = '<div style="border-bottom: 2px solid #eee; color: #147cc2; font-size: 20px; font-weight: bold;">'.$tab->label.'<br></div>';
			$html .= '<p>'.$tab->content->data.'</p>';
			$pdf->writeHTML($html, true, false, true, false, '');
			
			if(isset($tab->content->tabel)) { 
				
				$pdf->AddPage();
				$html =  '<br/><br/>'.recursiveTabelData($tab->content->tabel);
				$pdf->writeHTML($html, true, false, true, false, '');
			}
		}

		$bookmark_templates = [];
		$bookmark_templates[0] = '
		<table border="0" cellpadding="10" cellspacing="0" style="border-bottom: 1px solid #eee; font-family:freesans;">
			<tr>
				<td width="130mm"><strong>#TOC_DESCRIPTION#</strong></td>
				<td width="40mm" align="left">#TOC_PAGE_NUMBER#</td>
			</tr>
		</table>';

		$bookmark_templates[1] = '
		<table border="0" cellpadding="10" cellspacing="0" style="font-family:freesans;">
			<tr>
				<td width="130mm">&nbsp;&nbsp;&nbsp;&nbsp;#TOC_DESCRIPTION#</td>
				<td width="40mm" align="left">#TOC_PAGE_NUMBER#</td>
			</tr>
		</table>';

		$bookmark_templates[2] = '
		<table border="0" cellpadding="10" cellspacing="0" style="font-family:freesans; font-size: 10pt">
			<tr>
				<td width="130mm">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#TOC_DESCRIPTION#</td>
				<td width="40mm" align="left">#TOC_PAGE_NUMBER#</td>
			</tr>
		</table>';

		// TOC setup
		$pdf->addTOCPage();	
		$pdf->SetFont('freesans', 'B', 16);
		$pdf->MultiCell(0, 0, 'תוכן עניינים', 0, 'C', 0, 1, '', '', true, 0);
		$pdf->SetFont('freesans', '', 12);

		$pdf->addHTMLTOC(1, 'תוכן עניינים', $bookmark_templates, true, 'B', array(128,0,0));
		$pdf->endTOCPage();
		
		// Output file code (see documentation)
		if(isset($_POST['output'])) $output = $_POST['output'];
		
		//Close and output PDF document
		$pdf->Output($fileName, $output );
	}

	// 	I : send the file inline to the browser (default). The plug-in is used if available. The name given by name is used when one selects the "Save as" option on the link generating the PDF.
	// 	D : send to the browser and force a file download with the name given by name.
	// 	F : save to a local server file with the name given by name.
	// 	S : return the document as a string (name is ignored).
	// 	FI : equivalent to F + I option
	// 	FD : equivalent to F + D option
	// 	E : return the document as base64 mime multi-part email attachment (RFC 2045)

	// helpers functions 
	function recursiveTabelData($data, $html='', $level = 0 ) {
		global $pdf;
		$data = json_decode(json_encode($data),1);

		foreach($data as $v) {
			
			if($level > 1)	{
				
				$html .= 
				'<table border="1" cellpadding="10" cellspacing="0" bgcolor="#fff" color="#000">
					<tr>
						<td>'.$v['teurkurs'].'</td>
						<td>שעות: '.$v['shaot1'].'</td>
					</tr>
				</table>';	

			}else {

				$style = ['border' => 0, 'bgcolor' => '#057', 'color' => '#fff' ];
				if( $level == 1) $style = ['border' => 0 ,'bgcolor' => '#147cc2', 'color' => '#fff' ];
				
				$pdf->Bookmark($v['teurrama'], $level + 1, 0, '', 'B', array(0,0,0));

				$html .= 
				'<table border="'.$style['border'].'" cellpadding="10" cellspacing="0" bgcolor="'.$style['bgcolor'].'" color="'.$style['color'].'">
					<tr>
						<td>
							<h4>'.$v['teurrama'].'</h4>
							<p>'.$v['hesber'].'</p>
						</td>
					</tr>
				</table>';			
			}

			if(isset($v['rama']))
				$html = recursiveTabelData($v['rama'], $html , 1);
			elseif(isset($v['kurs']))
				$html = recursiveTabelData($v['kurs'], $html , 2);
		}

		return $html;	
	}

?>