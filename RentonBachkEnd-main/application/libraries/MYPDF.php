<?php
	require_once(APPPATH . '/third_party/TCPDF-6.3.5/tcpdf.php');
	
	class MYPDF extends TCPDF{
		
		private $CI;
		
		function __construct() {
			parent::__construct();
			$this->CI =& get_instance();
			$this->CI->load->database();
			$this->CI->load->library('ion_auth');
		}
		
		//Page header
		public function aHeader() {
			$this->CI->db->where('name','report_title');
			$report_title = $this->CI->db->get('config')->row()->value;
			$this->CI->db->where('name','report_description');
			$report_description = $this->CI->db->get('config')->row()->value;
			// Logo
			
			$image_file = FCPATH.'data/default/logo.png';
			$this->Image($image_file, 0, 5, 15, '', 'PNG', '', 'C', false, 300, 'C', false, false, 0, false, false, false);
			
			$this->SetY(20);
			// Set font
			$this->SetFont('times', 'B', 14);
			// Title
			$this->Cell(0, 15,$report_title, 0, false, 'C', 0, '', 0, false, 'M', 'M');
			$this->Ln(5);
			$this->SetFont('times', '', 10);
			//$this->setCellPaddings(-10, 5, 5,5);
			
			//$this->Cell(0, 15,$report_description, 0, false, 'C', 0, '', 0, false, 'M', 'M');
			//$this->Text(0, 15,$report_description, 0, false, 'C', 0, '', 0, false, 'M', 'M');
			$this->Write(0, $report_description, '', 0, 'C', true, 0, false, false, 0);
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
			$this->Ln(5);
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
				} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
			
		}
		
		public function Header(){
			$this->CI->db->where('name','report_title');
			$report_title = $this->CI->db->get('config')->row()->value;
			$this->CI->db->where('name','report_description');
			$report_description = $this->CI->db->get('config')->row()->value;
			$image_file = FCPATH.'data/default/logo.png';
			
			if ($this->header_xobjid === false) {
				// start a new XObject Template
				$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
				$headerfont = $this->getHeaderFont();
				$headerdata = $this->getHeaderData();
				$headerdata['logo_width'] = 20;
				$this->y = $this->header_margin;
				if ($this->rtl) {
					$this->x = $this->w - $this->original_rMargin;
					} else {
					$this->x = $this->original_lMargin;
				}
				if (($image_file) AND ($image_file != K_BLANK_IMAGE)) {
					$imgtype = TCPDF_IMAGES::getImageFileType($image_file);
					if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
						$this->ImageEps($image_file, '', '', $headerdata['logo_width']);
						} elseif ($imgtype == 'svg') {
						$this->ImageSVG($image_file, '', '', $headerdata['logo_width']);
						} else {
						$this->Image($image_file, '', '', $headerdata['logo_width']);
					}
					$imgy = $this->getImageRBY();
					} else {
					$imgy = $this->y;
				}
				$cell_height = $this->getCellHeight($headerfont[2] / $this->k);
				// set starting margin for text data cell
				if ($this->getRTL()) {
					$header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
					} else {
					$header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
				}
				$cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
				$this->SetTextColorArray($this->header_text_color);
				// header title
				$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
				$this->SetX($header_x);
				$this->Cell($cw, $cell_height, $report_title, 0, 1, '', 0, '', 0);
				// header string
				$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
				$this->SetX($header_x);
				$this->MultiCell($cw, $cell_height, $report_description, 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
				// print an ending header line
				$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
				$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
				if ($this->rtl) {
					$this->SetX($this->original_rMargin);
					} else {
					$this->SetX($this->original_lMargin);
				}
				$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
				$this->endTemplate();
			}
			// print header template
			$x = 0;
			$dx = 0;
			if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
				// adjust margins for booklet mode
				$dx = ($this->original_lMargin - $this->original_rMargin);
			}
			if ($this->rtl) {
				$x = $this->w + $dx;
				} else {
				$x = 0 + $dx;
			}
			$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
			if ($this->header_xobj_autoreset) {
				// reset header xobject template at each page
				$this->header_xobjid = false;
			}
		}
		// Page footer
		public function aFooter() {
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', 'I', 8);
			// Page number
			$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		}
		
		public function Footer()
		{
			$user = $this->CI->ion_auth_model->user($this->CI->session->userdata('user_id'))->row();
			
			$cur_y = $this->y;
			$this->SetTextColorArray($this->footer_text_color);
			//set style for cell border
			$line_width = (0.85 / $this->k);
			$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
			//print document barcode
			$barcode = $this->getBarcode();
			if (!empty($barcode)) {
				$this->Ln($line_width);
				$barcode_width = round(($this->w - $this->original_lMargin - $this->original_rMargin) / 3);
				$style = array(
				'position' => $this->rtl?'R':'L',
				'align' => $this->rtl?'R':'L',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'padding' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false,
				'text' => false
				);
				$this->write1DBarcode($barcode, 'C128', '', $cur_y + $line_width, '', (($this->footer_margin / 3) - $line_width), 0.3, $style, '');
			}
			$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
			if (empty($this->pagegroups)) {
				$pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
				} else {
				$pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
			}
			$this->SetY($cur_y);
			//Print page number
			if ($this->getRTL()) {
				$this->SetX($this->original_rMargin);
				$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
				} else {
				$this->SetX($this->original_lMargin);
				$this->Cell(0, 0, $user->first_name." ".$user->last_name." ( ".strftime('%A, %d %B %Y %H:%M',strtotime(date('Y-m-d H:i:s')))." )", 'T', 0, 'L');
				$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
			}
		}
	}	