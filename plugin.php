<?php

class pluginSummariseChildrenInParent extends Plugin {

	public function init()
	{
		$this->dbFields = array(
			'enableForStatic'=>true,
			'enableForSticky'=>true,
			'enableForNormal'=>true
		);
	}

	public function beforeSiteLoad()
	{
		$login = new Login();

		if ( $login->isLogged()) {
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

		return $html;
	}
	
	public function siteHead()
	{
		// Include plugin's CSS files
		$html = $this->includeCSS('summary-content-style.css');

		return $html;
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

		$html .= '</div>';// Close class="SummariseChildrenInParent-plugin"
		
		return $html;
	}

	// Admin Config Form


	public function pageEnd()
	{
		global $L;
		global $url;
		global $site;
		global $pages;
		global $page;

		// Check if the page has children
		if ( ($page->hasChildren()) and ( $page->custom('show') )  ) 
		{

			$formatter = new NumberFormatter(@$locale,  NumberFormatter::CURRENCY);

			$html .= '<h4>'.$L->get('summary-label').'</h4> ';
			
			// Get the list of children
			$children = $page->children();
			foreach ($children as $child) {


				$cost = $formatter->formatCurrency($child->custom('Cost'), "GBP") . "<br>";

				$eventDate = IntlDateFormatter::formatObject(
									IntlCalendar::fromDateTime($child->custom('EventDate'))
								,	"eee dd MMM yyyy"	//UCI standard formatted string
								,	@$locale );
				$openDate	= IntlDateFormatter::formatObject(
									IntlCalendar::fromDateTime($child->custom('OpenDate'))
								,	"dd/MM/yyyy"	//UCI standard formatted string
								,	@$locale );
				$closeDate	= IntlDateFormatter::formatObject(
									IntlCalendar::fromDateTime($child->custom('CloseDate'))
								,	"dd/MM/yyyy"	//UCI standard formatted string
								,	@$locale );

				$bulletImage = $child->thumbCoverImage();
				if (empty($bulletImage)) {
					preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $child->content(), $matches);
					if (isset($matches[1][0])) {
						$bulletImage = $matches[1][0];
					}
				}	

				$html .= '<article class="home-page hentry">';
				$html .= '<div class="entry-header-bg" ';
				if( !empty($bulletImage) ) { 
					$html .=  'style="background-image:url('.$bulletImage .')" >';
					}

				$html .= '	<a class="entry-header-bg-link" href="'. $child->permalink() .'" rel="bookmark">';
					if(empty($bulletImage)){
						$html .= '<svg class="icon icon-pencil" aria-hidden="true" role="img">
									<use xlink:href="#icon-pencil"></use> </svg>';
					}
				$html .= '		<span class="screen-reader-text">';
				$html .= 			$L->get('Continue reading') . ' ' . $child->title() . PHP_EOL ;
				$html .= '		</span>';
				$html .= '	</a> ';
				$html .= '</div>';

				$html .= '<div class="entry-inner">';
				$html .= '	<header class="entry-header">';
				$html .= '		<h5 class="entry-title title-font text-italic">';
				$html .= '			<a href="' . $child->permalink().'" rel="bookmark">'.$child->title() . " ~ $eventDate ~ $cost</a>";
				$html .= '		</h5>';
				$html .= 		"Tickets available from $openDate to $closeDate";					
				$html .= '	</header>';

				$html .= '	<div class="entry-summary">';


				if(strlen($child->description()) > 0 ){
                    $html .= $child->description();
				}
				else {
					$html .= $this->content2excerpt($child->content(false));
				}
				$html .= '	</div>';

				$html .= '	<div class="entry-comment grid-same-line">';
				$html .= '		<a class="more-link underline-link medium-font-weight" href="' . $child->permalink(). '" role="button">Read more</a>';
				$html .= '	</div>';
				$html .= '</div>';

				$html .= '</article><hr>';
				
			}
		}

		return $html;

	}

	public function pageBegin()
	{
		global $L;
		//global $url;
		//global $site;
		//global $pages;
		global $page;

		$formatter = new NumberFormatter(@$locale,  NumberFormatter::CURRENCY);
		$isChild = false;
	
		$parentKey = $page->parentKey();
		if($parentKey!==false) {
			$isChild = true;
		}
		$show = $page->custom('show');

		IF ( (strtotime($page->date())) < (strtotime('01-09-2019')) ) {$show = false;}
		
		$html = '';

		// Check if the page has children
		if ( ( $isChild ) and ( $show ) )
		{

			$cost = $formatter->formatCurrency($page->custom('Cost'), "GBP");

			$eventDate = IntlDateFormatter::formatObject(
								IntlCalendar::fromDateTime($page->custom('EventDate'))
							,	"eee dd MMMM yyyy"	//UCI standard formatted string
							,	@$locale );

			$eventTime = IntlDateFormatter::formatObject(
								IntlCalendar::fromDateTime($page->custom('EventDate'))
							,	"HH:mm"	//UCI standard formatted string
							,	@$locale );
			IF ($eventTime = '00:00') {$eventTime = 'TBC';}
			
			$openDate = IntlDateFormatter::formatObject(
								IntlCalendar::fromDateTime($page->custom('OpenDate'))
							,	"dd/MM/yyyy"	//UCI standard formatted string
							,	@$locale );

			$closeDate = IntlDateFormatter::formatObject(
								IntlCalendar::fromDateTime($page->custom('CloseDate'))
							,	"dd/MM/yyyy"	//UCI standard formatted string
							,	@$locale );

			$venueLocation = $page->custom('Venue');

			$eventDuration = $page->custom('EventDuration');
			IF ((empty($eventDuration)) OR ($eventDuration = '')) { $eventDuration = 'TBC'; }

			$html .= '<div class="SummariseChildrenInParent-plugin">';

			$html .= '<div><p><strong>'.$L->get('at-a-glance').'</strong></p></div>';
			$html .= '<pre><code>';
			$html .= '<div class="divTable" style="width: 100%;" ><div class="divTableBody">';

			$html .= '<div class="divTableRow">';
			$html .= '<div class="divTableCell">'."Event Date: $eventDate</div>";
			$html .= '<div class="divTableCell">'."Event Time: $eventTime</div>";
			$html .= '</div>';

			$html .= '<div class="divTableRow">';
			$html .= '<div class="divTableCell">'."Venue/Location: $venueLocation</div>";
			$html .= '<div class="divTableCell">'."Running Time: $eventDuration</div>";
			$html .= '</div>';

			$html .= '<div class="divTableRow">';
			$html .= '<div class="divTableCell">'."Order Tickets: $openDate to $closeDate</div>";
			$html .= '<div class="divTableCell">'."Cost: $cost</div>";
			$html .= '</div>';

			$html .= '</div></div>';
			$html .= '</code></pre>';

			$html .= '</div>';// Close class="SummariseChildrenInParent-plugin"

		}

		return $html;

	}

	public function content2excerpt($cont,  $limit=260, $ending = '...'  )
	{
		$cont = str_replace('<', ' <', $cont);
		$cont = html_entity_decode($cont, ENT_QUOTES | ENT_HTML5, "UTF-8");
		$descr = $this->truncate2nearest_word(Text::removeHTMLTags($cont), $limit, $ending);
		$descr = trim($descr);
		return $descr;
	}

	public function truncate2nearest_word($text, $limit, $ending = '...') {
		$text = str_replace('  ', ' ', $text); // replace repeated whitespace
		$text = substr($text, 0, strrpos(substr($text, 0, $limit), ' '));
		$text = trim($text);
		$text .= $ending;
		return $text;
	}


}