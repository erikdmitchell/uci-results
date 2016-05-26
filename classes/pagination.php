<?php

/**
 * UCIcURLPagination class.
 */
class UCIcURLPagination {

	public $pagenum=0;
	public $limit=0;
	public $offset=0;
	public $total=0;
	public $num_of_pages=0;
	public $base_url='';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @param int $total (default: 0)
	 * @param int $limit (default: 15)
	 * @return void
	 */
	public function __construct($total=0, $limit=15) {
		$this->pagenum=isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 1;
		$this->limit=$limit;
		$this->offset=($this->pagenum-1)*$limit;
		$this->total=$total;
		$this->num_of_pages=ceil($this->total/$this->limit);
		$this->base_url=get_current_admin_page_url();
	}

	/**
	 * display_pagination function.
	 *
	 * @access public
	 * @return void
	 */
	public function display_pagination() {
		echo $this->get_pagination();
	}

	/**
	 * get_pagination function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_pagination() {
		$html=null;

		$html.='<div class="ucicurl-pagination">';
			$html.=$this->prev_link();
			$html.=$this->next_link();
		$html.='</div>';

		return $html;
	}

	/**
	 * next_link function.
	 *
	 * @access public
	 * @param string $text (default: 'Next')
	 * @return void
	 */
	public function next_link($text='Next') {
		$html=null;
		$page=$this->pagenum+1;
		$class='';

		if ($page>$this->num_of_pages)
			$class=' hide';

		$html.='<div class="next-link'.$class.'">';

			if ($page<=$this->num_of_pages)
				$html.='<a href="'.$this->base_url.'&pagenum='.$page.'">'.$text.'</a>';

		$html.='</div>';

		return $html;
	}

	/**
	 * prev_link function.
	 *
	 * @access public
	 * @param string $text (default: 'Previous')
	 * @return void
	 */
	public function prev_link($text='Previous') {
		$html=null;
		$page=$this->pagenum-1;
		$class='';

		if ($page<=0)
			$class=' hide';

		$html.='<div class="prev-link'.$class.'">';

			if ($page!=0)
				$html.='<a href="'.$this->base_url.'&pagenum='.$page.'">'.$text.'</a>';

		$html.='</div>';

		return $html;
	}

}
?>