<?php
/**
 * PHPExcelHandler 
 * @author Eason Luo
 * @since 25/5/2014
 *
 */

require_once  dirname(__FILE__).'/../../lib/phpexcel/PHPExcel.php';
class PHPExcelHandler {
	
	/**
	 * excel column title to match the event info
	 *
	 * @var array
	 */
	private $excel;
	private $title = array (
			'A' => 'event_name',
			'B' => 'description',
			'C' => 'provider',
			'D' => 'category',
			'E' => 'require_booking',
			'F' => 'int_std_recommended',
			'G' => 'location',
			'H' => 'requested_venue',
			'I' => 'event_date',
			'J' => 'start_time',
			'K' => 'end_time',
			'L' => 'new_student',
			'M' => 'returning_student',
			'N' => 'undergraduate',
			'O' => 'postgraduate_coursework',
			'P' => 'postgraduate_research',
			'Q' => 'url_link'
	);
	
	/**
	 *
	 * @param string $filename        	
	 */
	public function __construct($filename, $title = array()) {
		require_once dirname ( __FILE__ ) . '/Format.class.php';
		$fileType = pathinfo ( $filename, PATHINFO_EXTENSION ) === 'xlsx' ? 'Excel2007' : 'Excel5';
		$excelReader = PHPExcel_IOFactory::createReader ( $fileType );
		$this->excel = $excelReader->load ( $filename );
		$this->title = $title;
	}
	
	/**
	 * handle file uploading and parsing error by errorcode
	 *
	 * @param int $errorCode        	
	 */
	public function handleError($errorCode) {
	}
	
	/**
	 * get column title by column number
	 *
	 * @param string $column        	
	 * @return string
	 */
	public function getTitle($column) {
		return isset ( $this->title [$column] ) ? $this->title [$column] : "";
	}
	/**
	 * check if the column is acceptable
	 * @param string $column
	 * @return boolean
	 */
	public function accept($column){
		return isset($this->title[$column]); 
	}
	public function getValueAt($column, $row, $calculated = false) {
		$sheet = $this->excel->getActiveSheet ();
		$cell = $sheet->getCellByColumnAndRow ( ord ( $column ) - 65, $row );
		$val = $calculated ? $cell->getCalculatedValue () : $cell->getValue ();
		// TODO $val = iconv('utf-8', 'gbk', $val);
		return is_string ( $val ) ? trim ( $val ) : $val;
	}
	public function activate($columnIndex) {
		$this->excel->setActiveSheetIndex ( $columnIndex );
	}
	public function getHighestColumn() {
		return $this->excel->getActiveSheet ()->getHighestColumn ();
	}
	public function getHighestRow() {
		return $this->excel->getActiveSheet ()->getHighestRow ();
	}
	public function getSheetCount() {
		return $this->excel->getSheetCount ();
	}
	public function convertDate($val) {
		return date ( 'Y-m-d', PHPExcel_Shared_Date::ExcelToPHP ( $val ) );
	}
	public function convertTime($val) {
    // echo $val;
    // echo date ( 'H:i:s', PHPExcel_Shared_Date::ExcelToPHP ( $val ) );
		return date ( 'H:i:s', PHPExcel_Shared_Date::ExcelToPHP ( $val ) );
	}
	public function convertString($val) {
		if (is_string ( $val ) && empty ( $val )) {
			return "";
		}
		return $val;
	}
	public function getSheetName() {
		$sheet = $this->excel->getActiveSheet ();
		return $sheet->getTitle ();
	}
	
	/**
	 *
	 * @param object $val        	
	 * @return number
	 */
	public function convertBoolean($val) {
		return Format::zerone ( $val );
	}

}

?>
