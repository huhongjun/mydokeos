
/*******************************************************************************
** 
** Filename: SCOFunctions.js
**
** File Description: This file contains several JavaScript functions that are 
**                   used by the Sample SCOs contained in the Sample Course.
**                   These functions encapsulate actions that are taken when the
**                   user navigates between SCOs, or exits the Lesson.
**
** Author: ADL Technical Team
**
** Contract Number:
** Company Name: CTC
**
** Design Issues:
**
** Implementation Issues:
** Known Problems:
** Side Effects:
**
** References: ADL SCORM
**
********************************************************************************
**
** Concurrent Technologies Corporation (CTC) grants you ("Licensee") a non-
** exclusive, royalty free, license to use, modify and redistribute this
** software in source and binary code form, provided that i) this copyright
** notice and license appear on all copies of the software; and ii) Licensee
** does not utilize the software in a manner which is disparaging to CTC.
**
** This software is provided "AS IS," without a warranty of any kind.  ALL
** EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING ANY
** IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE OR NON-
** INFRINGEMENT, ARE HEREBY EXCLUDED.  CTC AND ITS LICENSORS SHALL NOT BE LIABLE
** FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF USING, MODIFYING OR
** DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES.  IN NO EVENT WILL CTC  OR ITS
** LICENSORS BE LIABLE FOR ANY LOST REVENUE, PROFIT OR DATA, OR FOR DIRECT,
** INDIRECT, SPECIAL, CONSEQUENTIAL, INCIDENTAL OR PUNITIVE DAMAGES, HOWEVER
** CAUSED AND REGARDLESS OF THE THEORY OF LIABILITY, ARISING OUT OF THE USE OF
** OR INABILITY TO USE SOFTWARE, EVEN IF CTC  HAS BEEN ADVISED OF THE
** POSSIBILITY OF SUCH DAMAGES.
**
*******************************************************************************/
var startDate;
var exitPageStatus;

function loadPage()
{	
	if(top.document.readyState != "complete") {
		setTimeout("loadPage()", 200);
		return;
	}
	window.onbeforeunload=unloadPage;
   var result = doLMSInitialize();
   var status = doLMSGetValue( "cmi.core.lesson_status" );
   if (status == "not attempted")
   {
	  // the student is now attempting the lesson
	  doLMSSetValue( "cmi.core.lesson_status", "browsed" );
   }
//   doLMSSetParentValue( "cmi.core.lesson_status", "completed" );
   
   exitPageStatus = true;
   startTimer();
  
  if(result == "true" && oLMSAPI.m_oContentViewer){
  	
		re = /([^\/]+\/[^\/]+\/[^\/]+)$/i;
		var thisIdentifier = oLMSAPI.m_oContentViewer.GetByResource( this.location.href.toString().substring( this.location.href.search(re) , 430000) ).getAttribute("identifier") ;
		var lmsappiIdentifier  = oLMSAPI.LRNGetCurrent().documentElement.firstChild.getAttribute("ID");
		if( thisIdentifier != lmsappiIdentifier ){
			oLMSAPI.LRNDoSelect(thisIdentifier);
		}
	}

}


function startTimer()
{
   try
   {
	   startDate = new Date().getTime();
	   doLMSSetValue( "lrn.item.lastaccess", new Date().toUTCString());
   } catch(e){
   }
}

function computeTime()
{
   if ( startDate != 0 )
   {
      var currentDate = new Date().getTime();
      var elapsedSeconds = ( (currentDate - startDate) / 1000 );
      var formattedTime = convertTotalSeconds( elapsedSeconds );
   }
   else
   {
      formattedTime = "00:00:00.0";
   }

   doLMSSetValue( "cmi.core.session_time", formattedTime );
}

function doBack()
{
   doLMSSetValue( "cmi.core.exit", "suspend" );

   computeTime();
   exitPageStatus = true;
   
   var result;

   result = doLMSCommit();

	// NOTE: LMSFinish will unload the current SCO.  All processing
	//       relative to the current page must be performed prior
	//		 to calling LMSFinish.   
  
}

function doContinue( status )
{
   // Reinitialize Exit to blank
   doLMSSetValue( "cmi.core.exit", "" );

   var mode = doLMSGetValue( "cmi.core.lesson_mode" );

   if ( mode != "review"  &&  mode != "browse" )
   { 
      doLMSSetValue( "cmi.core.lesson_status", status );
   }
 
   computeTime();
   exitPageStatus = true;
   
   var result;
   result = doLMSCommit();
	// NOTE: LMSFinish will unload the current SCO.  All processing
	//       relative to the current page must be performed prior
	//		 to calling LMSFinish.   
}

function doQuit()
{
   doLMSSetValue( "cmi.core.exit", "suspend" );

   computeTime();
   exitPageStatus = true;
   
   var result;

   result = doLMSCommit();

	// NOTE: LMSFinish will unload the current SCO.  All processing
	//       relative to the current page must be performed prior
	//		 to calling LMSFinish.   

   result = doLMSFinish();
   	window.onbeforeunload=null;
}

function setLessonStatus(status) {
// 	if(oLMSAPI){
		switch(status){
			case 'failed':doLMSSetValue( "cmi.core.lesson_status", "failed" ); break; //red dot
			case 'not attempted':doLMSSetValue( "cmi.core.lesson_status", "not attempted" ); break; //empty clipboard
			case 'completed':doLMSSetValue( "cmi.core.lesson_status", "completed" ); break; //black dot - goto next
			case 'incomplete':doLMSSetValue( "cmi.core.lesson_status", "incomplete" ); break; //white-black dot
			case 'passed':doLMSSetValue( "cmi.core.lesson_status", "passed" ); break; //green dot 
			case 'browsed':doLMSSetValue( "cmi.core.lesson_status", "browsed" ); break; //white dot
		}
//	}
}

function getLessonStatus() {
//	if(oLMSAPI) {
		return doLMSGetValue('cmi.core.lesson_status');
//	}
}

//Fill up comments
function setComments(i) {    
		completed=true;
		if(oLMSAPI) {
			var currentCommentObj = document.all("r"+i);
			if(currentCommentObj ){
				var currentComment = currentCommentObj.innerText;
				var previousComment = doLMSGetValue('cmi.comments').toString();
				var newComment ="";
				if(currentComment != ""){
					var toDayDate =new Date();
					var toDayString = toDayDate.getDate() + "/" + (toDayDate.getMonth() +1) + "/" +   toDayDate.getFullYear() + " "  + toDayDate.getHours() + ":" + toDayDate.getMinutes() + ":"  + toDayDate.getSeconds();
					newComment = currentComment  + "\r" + "(" + toDayString.toString() + ")\r\r"  +   previousComment.toString()
				} else {
					newComment = previousComment;
				}
				
				doLMSSetValue("cmi.comments",  newComment);
				oLMSAPI.LRNDoNext();
			}
			/*if(i < 23)else
				oLMSAPI.LRNDoSelectFirst(); */
		} else {
			//if(i < 23)
				//document.location.href = document.location.href.substring(0, document.location.href.length - 6) + (i>8?"":"0") + (i+1) + ".htm";
				objNav.next();
		//	else {
		//		document.location.href = document.location.href.substring(0, document.location.href.length - 28) + "Module0/Section0/page01.htm";
		//	}
		}
}




/*******************************************************************************
** The purpose of this function is to handle cases where the current SCO may be 
** unloaded via some user action other than using the navigation controls 
** embedded in the content.   This function will be called every time an SCO
** is unloaded.  If the user has caused the page to be unloaded through the
** preferred SCO control mechanisms, the value of the "exitPageStatus" var
** will be true so we'll just allow the page to be unloaded.   If the value
** of "exitPageStatus" is false, we know the user caused to the page to be
** unloaded through use of some other mechanism... most likely the back
** button on the browser.  We'll handle this situation the same way we 
** would handle a "quit" - as in the user pressing the SCO's quit button.
*******************************************************************************/
function unloadPage()
{
   doLMSSetValue( "cmi.core.exit", "suspend" );

   computeTime();
	setCompleteStatus();

	if (exitPageStatus != true)
	{
		doQuit();
	} else {
		doLMSCommit();
	}

	// NOTE:  don't return anything that resembles a javascript
	//		  string from this function or IE will take the
	//		  liberty of displaying a confirm message box.
}

/*******************************************************************************
** this function will convert seconds into hours, minutes, and seconds in
** CMITimespan type format - HHHH:MM:SS.SS (Hours has a max of 4 digits &
** Min of 2 digits
*******************************************************************************/
function convertTotalSeconds(ts)
{
   var sec = (ts % 60);

   ts -= sec;
   var tmp = (ts % 3600);  //# of seconds in the total # of minutes
   ts -= tmp;              //# of seconds in the total # of hours

   // convert seconds to conform to CMITimespan type (e.g. SS.00)
   sec = Math.round(sec*100)/100;
   
   var strSec = new String(sec);
   var strWholeSec = strSec;
   var strFractionSec = "";

   if (strSec.indexOf(".") != -1)
   {
      strWholeSec =  strSec.substring(0, strSec.indexOf("."));
      strFractionSec = strSec.substring(strSec.indexOf(".")+1, strSec.length);
   }
   
   if (strWholeSec.length < 2)
   {
      strWholeSec = "0" + strWholeSec;
   }
   strSec = strWholeSec;
   
   if (strFractionSec.length)
   {
      strSec = strSec+ "." + strFractionSec;
   }


   if ((ts % 3600) != 0 )
      var hour = 0;
   else var hour = (ts / 3600);
   if ( (tmp % 60) != 0 )
      var min = 0;
   else var min = (tmp / 60);

   if ((new String(hour)).length < 2)
      hour = "0"+hour;
   if ((new String(min)).length < 2)
      min = "0"+min;

   var rtnVal = hour+":"+min+":"+strSec;

   return rtnVal;
}
