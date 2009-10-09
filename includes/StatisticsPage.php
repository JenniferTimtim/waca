<?php

abstract class StatisticsPage
{
	
	/**
	 * Creates a statistics page.
	 * 
	 * @param $pageName Name of the page
	 * @return Object of type dependant on the name specified.
	 */
	public static function Create($pageName)
	{
		// calculate the name of the statistics page
		$statsPage = "Stats" . $pageName;
		
		global $filepath;	
		// check the stats page definition exists...
		if(file_exists($filepath . "/includes/statistics/Stats" . $pageName . ".php"))
		{	// and include it.
			require_once($filepath . "/includes/statistics/Stats" . $pageName . ".php");
		}
		else
		{	// class def doesn't exist: error
			die("Unknown statistics page: " + $statsPage);
		}
	
		// ok, so the file where the class def should be exists, but we need to check the class
		// itself exists. 
		if(class_exists($statsPage))
		{	// the class exists, all is ok.
			
			// create the stats page object
			$object = new $statsPage;
			
			// check the newly created object has inherits from StatisticsPage class
			if(get_parent_class($object)=="StatisticsPage")
			{
				// all is good, return the new statistics page object
				return $object;
			}
			else
			{
				// oops. this is our class, named correctly, but it's a bad definition.
				die("Unrecognised statistics page definition.");
			}
		}
		else
		{
			// file exists, but no definition of the class
			die("No definition for statistics page: " + $statsPage);
		}
	}
	
	/**
	 * Abstract method provides the content of the statistics page
	 * @return string. content of stats page.
	 */
	abstract protected function execute();
	
	/**
	 * Returns the title of the page (initial header, and name in menu)
	 * @return string.
	 */
	abstract public function getPageTitle();
	
	/**
	 * Returns the name of the page (used in urls, and class defs)
	 * @return string.
	 */
	abstract public function getPageName();
	
	/**
	 * Determines if the stats page is only available to logged-in users, or everyone.
	 * @return bool.
	 */
	abstract public function isProtected();
	
	/**
	 * Determines if the statistics page requires the wiki database. Defaults to true
	 * @return bool. 
	 */
	public function requiresWikiDatabase()
	{
		return true;
	}
	
	/**
	 * Shows the statistics page.
	 * @return null.
	 */
	public function Show()
	{
		// resume SESSION
		session_start();
		$sessionuser = ( isset($_SESSION['user']) ? $_SESSION['user'] : "");
		
		// fetch and show page header
		global $messages, $dontUseWikiDb;
		makehead( $sessionuser );
		
		if($this->requiresWikiDatabase() && ($dontUseWikiDb == 1))
		{	// wiki database unavailable, don't show stats page
			echo "<div id=\"content\"><h1>Error</h1><span style=\"color:red;font-weight:bold\">This statistics page is currently unavailable.</span>";
		}
		else
		{	// wiki database available OR stats page doesn't need wiki database
			// check protection level
			if($this->isProtected())
			{
				// protected, check accesslevel.
				$sessionuser = ( isset($_SESSION['user']) ? $_SESSION['user'] : "");
				if( !(hasright($sessionuser, "Admin") || hasright($sessionuser, "User")))
				{ // not authed
			
					echo "<div id=\"content\"><h1>Error</h1><span style=\"color:red;font-weight:bold\">You are not authorized to use this feature. Only logged in users may use this statistics page.</span>";
				}
				else
				{ // ok
			
					echo '<div id="content"><h1>' . $this->getPageTitle() . '</h1>';
					echo $this->execute();
				}
			}
			else
			{
				// not protected
				echo '<div id="content"><h1>' . $this->getPageTitle() . '</h1>';
				echo $this->execute();
			}
		}
		
		// show footer
		echo $messages->getMessage(22);
		
	}
	
}