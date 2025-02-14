<?php
/**
==============================================================================
*	This script displays the footer that is below (almost)
*	every Dokeos web page.
*
*	@package dokeos.include
==============================================================================
*/

/**** display of tool_navigation_menu according to admin setting *****/
if(api_get_setting('show_navigation_menu') != 'false')
{

   $course_id = api_get_course_id();
   if ( !empty($course_id) && ($course_id != -1) )
   {
   		if( api_get_setting('show_navigation_menu') != 'icons')
		{
	    	echo '</div> <!-- end #center -->';
    		echo '</div> <!-- end #centerwrap -->';
		}
      	require_once(api_get_path(INCLUDE_PATH)."tool_navigation_menu.inc.php");
      	show_navigation_menu();
   }
}
/***********************************************************************/

?>
 <div class="clear">&nbsp;</div> <!-- 'clearing' div to make sure that footer stays below the main and right column sections -->
</div> <!-- end of #main" started at the end of banner.inc.php -->

<div id="footer"> <!-- start of #footer section -->
<div id="bottom_corner"></div> 
 <div class="copyright">
  <?php global $_configuration; ?>
  <?php echo get_lang("Platform") ?> <a href="http://www.edlt.com">东方创远教育科技有限公司 <?php echo $_configuration['dokeos_version']; ?></a> &copy; <?php echo date('Y'); ?>
 </div>

<?php
/*
-----------------------------------------------------------------------------
	Plugins for footer section
-----------------------------------------------------------------------------
*/
api_plugin('footer');
?>
  <?php
  if (get_setting('show_administrator_data')=="true")
  	{
  	echo get_lang("Manager") ?> : <?php echo Display::encrypted_mailto_link(get_setting('emailAdministrator'),get_setting('administratorName')." ".get_setting('administratorSurname'));
	}
  ?>&nbsp;
  

</div> <!-- end of #footer -->

</div> <!-- end of #outerframe opened in header.inc.php -->

</body>
</html>