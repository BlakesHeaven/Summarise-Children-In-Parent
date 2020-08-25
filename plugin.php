<?php

class pluginSummariseChildrenInParent extends Plugin {

	public function init()
	{
		$this->dbFields = array(
			'enableForStatic'			=> true,
			'enableForSticky'			=> true,
			'enableForNormal'			=> true,
			'earliestDateToShowSummary'	=> '2019-09-01'	// Controls both 'Child Summary' on Parent page and 'At a Glance' summary on Child Page.
		);
	}

	public function beforeSiteLoad()
	{
		$login = new Login();

		IF ( $login->isLogged()) {
			$username = $login->username();
			$user = new User($username);

			$GLOBALS['userRole'] = $login->Role();
			$GLOBALS['userDisplayName'] =  $user->nickname() ?: $user->firstname() ?: $username; 
		}
		else {
			$GLOBALS['userRole'] = 'No Role';
			$GLOBALS['userDisplayName'] = 'No Name';
		}
	}

	public function adminHead()
	{
		// Include plugin's CSS files
		$html = $this->includeCSS('summary-content-style.css');
		RETURN $html;
	}

	public function siteHead()
	{
		// Include plugin's CSS files
		$html = $this->includeCSS('summary-content-style.css');
		$html .= '<link href="https://fonts.googleapis.com/css?family=Lora:400,400i,700|Roboto:400,400i,700" rel="stylesheet" />';
		
		RETURN $html;
	}

	// Admin Config Form
	public function form()
	{
		global $L;

		$html  = '<div class="alert alert-primary" role="alert">';
		$html .= $this->description();
		$html .= '</div>';

		$html = '<div class="SummariseChildrenInParent-plugin">';
		/********************************************************
			Global Options
		********************************************************/
		$html .= '<h3>'.$L->get('global-options-title').'</h3> ';
			
		$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';
			// Define the duration type
			$html .= '<div class="divTableCell">';
				$html .= '<label>'.$L->get('enable-for-static-label').'</label>';
				$html .= '<select name="enableForStatic">';
				$html .= '<option value="true" '.($this->getValue('enableForStatic')===true?'selected':'').'>'		.$L->get('Enabled').'</option>';
				$html .= '<option value="false" '.($this->getValue('enableForStatic')===false?'selected':'').'>'	.$L->get('Disabled').'</option>';
				$html .= '</select>';
				//$html .= '<span class="tip">'.$L->get('enable-for-static-label-tip').'</span>';
			$html .= '</div>';
			// Hide pages from menu for a particular category
			$html .= '<div class="divTableCell">';
				$html .= '<label>'.$L->get('enable-for-sticky-label').'</label>';
				$html .= '<select name="enableForSticky">';
				$html .= '<option value="true" '.($this->getValue('enableForSticky')===true?'selected':'').'>'		.$L->get('Enabled').'</option>';
				$html .= '<option value="false" '.($this->getValue('enableForSticky')===false?'selected':'').'>'	.$L->get('Disabled').'</option>';
				$html .= '</select>';
				//$html .= '<span class="tip">'.$L->get('enable-for-sticky-label-tip').'</span>';
			$html .= '</div>';
		$html .= '</div></div></div>';

		$html .= '<hr>';

		$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';
			$html .= '<div class="divTableCell">';
				$html .= '<label>'.$L->get('enable-for-normal-label').'</label>';
				$html .= '<select name="enableForNormal">';
				$html .= '<option value="true" '.($this->getValue('enableForNormal')===true?'selected':'').'>'		.$L->get('Enabled').'</option>';
				$html .= '<option value="false" '.($this->getValue('enableForNormal')===false?'selected':'').'>'	.$L->get('Disabled').'</option>';
				$html .= '</select>';
				//$html .= '<span class="tip">'.$L->get('enable-for-normal-label-tip').'</span>';
			$html .= '</div>';
		$html .= '</div></div></div>';

		$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody"><div class="divTableRow">';
			$html .= '<div class="divTableCell">';
				$html .= '<label>'.$L->get('define-earliest-date-label').'</label>';
				$html .= '<input name="earliestDateToShowSummary" type="text" placeholder="yyyy-mm-dd" value="'.$this->getValue('earliestDateToShowSummary').'">';
				$html .= '<span class="tip">'.$L->get('define-earliest-date-tip').'</span>';
			$html .= '</div>';
		$html .= '</div></div></div>';

		$html .= '</div>';// Close class="SummariseChildrenInParent-plugin"
		
		RETURN $html;
	}

	// Summary
	public function pageEnd()
	{
		global $L;
		global $page;		
		$earliestDateToShowSummary = strtotime( ($this->getValue('earliestDateToShowSummary')) );
		$show = $page->custom('show');
		$foundChildInScope = false;

		// Check IF the page has children
		IF ( ($page->hasChildren()) and ( $show ) ) 
		{
			$currencyFormatter = new NumberFormatter(@$locale,  NumberFormatter::CURRENCY);

			$html = '';
		
			// Get the list of children
			$children = $page->children();
			foreach ($children as $child) {

				IF ( strtotime($child->dateRaw()) >= $earliestDateToShowSummary )  {	// Define earliest Date in Admin

					$foundChildInScope = true;
					$eventDateRaw	= $child->custom('EventDate');
					$openDateRaw	= $child->custom('OpenDate');
					$closeDateRaw	= $child->custom('CloseDate');
					$costRaw		= $child->custom('Cost');
					$venueLocation	= $child->custom('VenueLocation');

					IF (empty($eventDateRaw)) {
						$eventDateFormated = '';
					}
					ELSE {
						$eventDateFormated = ' ~ '.IntlDateFormatter::formatObject(
														IntlCalendar::fromDateTime($eventDateRaw)
													,	"eee dd MMM yyyy"
													,	@$locale );
					}

					IF (empty($costRaw)) {
						$costFormated = '';
					}
					ELSE {
						$costFormated = ' ~ '.$currencyFormatter->formatCurrency($costRaw, "GBP") . "<br>";
					}

					IF (empty($openDateRaw)) {
						$openDateFormated = 'TBC';
					}
					ELSE {
					$openDateFormated = IntlDateFormatter::formatObject(
										IntlCalendar::fromDateTime($openDateRaw)
									,	"dd/MM/yyyy"
									,	@$locale );
					}
					
					IF (empty($closeDateRaw)) {
						$closeDateFormated = 'TBC';
					}
					ELSE {
					$closeDateFormated = IntlDateFormatter::formatObject(
										IntlCalendar::fromDateTime($closeDateRaw)
									,	"dd/MM/yyyy"	//UCI standard formatted string
									,	@$locale );
					}

					$bulletImage = $child->thumbCoverImage();
					IF (empty($bulletImage)) {
						preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $child->content(), $matches);
						IF (isset($matches[1][0])) {
							$bulletImage = $matches[1][0];
						}
					}	

					$html .= '<article class="home-page hentry">';
					$html .= '<div class="entry-header-bg" ';
					IF( !empty($bulletImage) ) { 
						$html .=  'style="background-image:url('.$bulletImage .')" >';
						}

					$html .= '	<a class="entry-header-bg-link" href="'. $child->permalink() .'" rel="bookmark">';
					IF(empty($bulletImage)){
						$html .= '<svg class="icon icon-pencil" aria-hidden="true" role="img"><use xlink:href="#icon-pencil"></use> </svg>';
					}
					$html .= '		<span class="screen-reader-text">';
					$html .= 			$L->get('Continue reading') . ' ' . $child->title() . PHP_EOL ;
					$html .= '		</span>';
					$html .= '	</a>';
					$html .= '</div>';

					$html .= '<div class="entry-inner">';
					$html .= '	<header class="entry-header">';
					$html .= '		<h5 class="entry-title title-font text-italic">';
					$html .= '			<a href="' . $child->permalink().'" rel="bookmark">'.$child->title() . "$eventDateFormated $costFormated</a>";
					$html .= '		</h5>';
					$html .= '		<h5 class="entry-title title-font text-italic">'.$venueLocation.'</h5>';

					IF ( ($openDateFormated <> 'TBC') AND ($closeDateFormated <> 'TBC') ) {
						$html .= 		$L->get('tickets-bookable-from')." $openDateFormated " . $L->get('tickets-bookable-to') . " $closeDateFormated";
					}
					
					$html .= '	</header>';

					$html .= '	<div class="entry-summary">';

					IF ( strlen($child->description()) > 0  ) {
						$html .= $child->description();
					}
					else {
						$html .= $this->content2excerpt( $child->content(false) );
					}
					$html .= '	</div>';

					$html .= '	<div class="entry-comment grid-same-line">';
					$html .= '		<a class="more-link underline-link medium-font-weight" href="' . $child->permalink(). '" role="button">Read more</a>';
					$html .= '	</div>';
					$html .= '</div>';
					$html .= '</article><hr>';
				}
			}
		}
		IF ( $foundChildInScope ) {$html = '<h4>'.$L->get('summary-label').'</h4> ' . $html; }

		RETURN $html;
	}

	public function pageBegin()
	{
		global $L;
		global $page;

		$currencyFormatter = new NumberFormatter(@$locale, NumberFormatter::CURRENCY);
		$isChild = false;
		$earliestDateToShowSummary = $this->getValue('earliestDateToShowSummary');
	
		$parentKey = $page->parentKey();
		IF($parentKey!==false) {
			$isChild = true;
		}
		$show = $page->custom('show');

		IF ( (strtotime($page->dateRaw())) < ( strtotime($earliestDateToShowSummary) ) ) { $show = false; } // Define earliest Date in Admin

		$html = '';

		// Check IF the page has children
		IF ( ( $isChild ) and ( $show ) )
		{
			$eventDateRaw	= $page->custom('EventDate');
			$costRaw 		= $page->custom('Cost');				
			$openDateRaw	= $page->custom('OpenDate');
			$closeDateRaw	= $page->custom('CloseDate');
			$venueLocation	= $page->custom('VenueLocation');
			$eventDuration	= $page->custom('EventDuration');
		
			
			IF (empty($eventDateRaw)) { 
				$eventDateFormated = 'TBC';
				$eventTimeFormatted = 'TBC';
			}
			ELSE {
				$eventTimeFormatted = IntlDateFormatter::formatObject(
									IntlCalendar::fromDateTime($eventDateRaw)
								,	"HH:mm"
								,	@$locale ).' hrs';
				IF ( ($eventTimeFormatted == '00:00 hrs') OR (empty($eventTimeFormatted)) ) {$eventTimeFormatted = 'TBC';}

				$eventDateFormated = IntlDateFormatter::formatObject(
									IntlCalendar::fromDateTime($eventDateRaw)
								,	"eee dd MMMM yyyy"
								,	@$locale );
			}

			IF (empty($costRaw)) {
				$costFormated = 'TBC';
			}
			ELSE {
				$costFormated = $currencyFormatter->formatCurrency($costRaw, "GBP");
			}

			IF (empty($openDateRaw)) { 
				$openDateFormated = 'TBC';
			}
			ELSE {
			$openDateFormated = IntlDateFormatter::formatObject(
								IntlCalendar::fromDateTime($openDateRaw)
							,	"dd/MM/yyyy"
							,	@$locale );
			}
			
			IF (empty($closeDateRaw)) { 
				$closeDateFormated = 'TBC';
			}
			ELSE {
			$closeDateFormated = IntlDateFormatter::formatObject(
								IntlCalendar::fromDateTime($closeDateRaw)
							,	"dd/MM/yyyy"
							,	@$locale );
			}

			IF ((empty($eventDuration)) OR ($eventDuration == '')) { $eventDuration = 'TBC'; }

			$html .= '<div class="SummariseChildrenInParent-plugin">';

			$html .= '<div><p><strong>'.$L->get('at-a-glance').'</strong></p></div>';
			$html .= '<pre><code>';
			$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody">';

			$html .= '<div class="divTableRow">';
			$html .= '<div class="divTableCell">'."Event Date: $eventDateFormated</div>";
			$html .= '<div class="divTableCell">'."Event Time: $eventTimeFormatted</div>";
			$html .= '</div>';

			$html .= '<div class="divTableRow">';
			$html .= '<div class="divTableCell">'."Venue: $venueLocation</div>";
			$html .= '<div class="divTableCell">'."Running Time: $eventDuration</div>";
			$html .= '</div>';

			$html .= '<div class="divTableRow">';
			$html .= '<div class="divTableCell">'."Order Tickets: $openDateFormated to $closeDateFormated</div>";
			$html .= '<div class="divTableCell">'."Cost: $costFormated</div>";
			$html .= '</div>';

			$html .= '</div></div>';
			$html .= '</code></pre>';

			$html .= '</div>';// Close class="SummariseChildrenInParent-plugin"

		}
		RETURN $html;
	}

	public function content2excerpt($cont,  $limit=260, $ending = '...'  )
	{
		$cont = str_replace('<', ' <', $cont);
		$cont = html_entity_decode($cont, ENT_QUOTES | ENT_HTML5, "UTF-8");
		$descr = $this->truncate2nearest_word(Text::removeHTMLTags($cont), $limit, $ending);
		$descr = trim($descr);
		RETURN $descr;
	}

	public function truncate2nearest_word($text, $limit, $ending = '...') {
		$text = str_replace('  ', ' ', $text); // replace repeated whitespace
		$text = substr($text, 0, strrpos(substr($text, 0, $limit), ' '));
		$text = trim($text);
		$text .= $ending;
		RETURN $text;
	}


}