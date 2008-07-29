//--- Menu Functions ---//
var MODULENAME = "Module";
var SECTIONNAME = "Section";
var PAGENAME = "page";
var DEFAULTSPATH =  "defaults/pages";
var DEFAULTMODULENAME =  "module";
var DEFAULTSSECTIONNAME =  "section";

// Write Flash Navigation Menu

/* TOP NAVIGATION */
function writeTopMenu() {
var ThisPageName
	if( !objNav.page && !objNav.section){
		ThisPageName= objNav.arrMenuText[objNav.module][0];
	} else if(!objNav.page && objNav.section){
		ThisPageName= objNav.arrMenuText[objNav.module][1][objNav.section][0];
	} else {
		ThisPageName= objNav.arrMenuText[objNav.module][1][objNav.section][1][objNav.page-1];
	}
	//var ThisPageName= objNav.arrMenuText[objNav.module][1][objNav.section][1][objNav.page-1];
	 
	//dw("<div  class=\"navOut\" style=\"text-align:left; padding:0px 0px 1px 10px;margin:0 0 0 0;width:778px;\">");
dw("<table  width=778 cellspacing=0 cellpadding=0 border=0><tr>");

if(objNav.getModuleCount() == 0){
	dw("<td class=\"exitSt\" onclick=\"return objNav.home()\">&nbsp;Home&nbsp;</td>");
	dw("<td width=2><img src=\"../../image/sp-orqnge.gif\" width=2 height=18 hspace=0></td>");		
}

	for(modId=0;modId<=objNav.getModuleCount(); modId++){
	ThisModuleName= objNav.arrMenuText[modId][0];
		if(objNav.module == modId){	
			//dw("<td class=\"mainNavOver\" >" + ThisModuleName + "</td>" );
			dw("<td class=\"mainNavOver\" ><a href=\"#\" onclick=\"return objNav.goModule("+ modId + ")\" class=\"topNavActive\">" + ThisModuleName + "</a></td>" );
		} else{
			dw("<td class=\"mainNavOut\" ><a href=\"#\" onclick=\"return objNav.goModule("+ modId + ")\" class=\"mainTopNav\">" + ThisModuleName + "</a></td>");
		}
		dw("<td width=2><img src=\"../../image/sp-orqnge.gif\" width=2 height=18 hspace=0></td>");		
	}
dw("<td class=\"exitSt\" onclick=\"objNav.doExit();return false;\" >Exit</td>");			
dw("</tr></table>");	
	//dw("</div>");	


	dw("<div  class=\"subnav\" >");
	for(secId=0;objNav.getSectionCount(objNav.module) != null && objNav.getSectionCount(objNav.module) >= secId; secId++){
		ThisSectionName= objNav.arrMenuText[objNav.module][1][secId][0];
		if(secId != objNav.getSectionCount(objNav.module)){
			gtElem="&gt;&nbsp;";
		} else{
			gtElem="";
		}
		if(objNav.section == secId){	
				//dw("<span class=\"topNavActive\" >" + ThisSectionName + "</span> "+gtElem);
				dw("<a href=\"#\" onclick=\"return objNav.goSection("+ secId + ")\" class=\"topNavActive\">" + ThisSectionName + "</a> "+gtElem);
			} else{
				dw("<a href=\"#\" onclick=\"return objNav.goSection("+ secId + ")\" class=\"topNav\">" + ThisSectionName + "</a> "+gtElem);
		}
	}
dw("</div>");
dw("<div class=\"subtitle\">" + ThisPageName + "</div>");
	
	
}
/* END TOP NAVIGATION */

/* MAIN NAVIGATION */
function writeMainMenu() {
var navString;
var ThisModuleName ;
var ThisSectionName ;
var ThisPageName;

if( !objNav.page && !objNav.section){
	ThisModuleName = objNav.arrMenuText[objNav.module][0];
	var navStringArray = new Array(ThisModuleName);
} else if(!objNav.page && objNav.section){
	ThisModuleName = objNav.arrMenuText[objNav.module][0];
	ThisSectionName = objNav.arrMenuText[objNav.module][1][objNav.section][0];
	var navStringArray = new Array(ThisModuleName, ThisSectionName);
} else {
	ThisModuleName = objNav.arrMenuText[objNav.module][0];
	ThisSectionName = objNav.arrMenuText[objNav.module][1][objNav.section][0];
	ThisPageName = objNav.arrMenuText[objNav.module][1][objNav.section][1][objNav.page-1];
	var navStringArray = new Array(ThisModuleName, ThisSectionName, ThisPageName);
}
if(objNav.getModuleCount() == 0){	navStringArray.shift();}
navString = navStringArray.join(" &gt; ");
	 
dw("<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"780\" >")
dw("<tr>")
dw("<td class=\"navOut\" width=100% style=\"text-align:left;cursor:default;padding-left:10px;white-space:normal;\">"+ navString + "&nbsp;</td>")

if( (!objNav.page && !objNav.section) || (!objNav.page && objNav.section)){
	dw("<td class=\"backOut\" onmouseover=\"this.className='backOver';\" onmouseout=\"this.className='backOut';\" onclick=\"return objNav.back()\"><IMG SRC='../../image/spacer.gif' width=52 height=44></td>")
}
for(pId=1;pId<=objNav.getPageCount(objNav.module, objNav.section); pId++){
	if(pId == (objNav.page)){
		dw("<td class=\"backOut\" onmouseover=\"this.className='backOver';\" onmouseout=\"this.className='backOut';\" onclick=\"return objNav.back()\"><IMG SRC='../../image/spacer.gif' width=52 height=44></td>")
		dw("<td class=\"navOver\" style=\"cursor:default;\">"+ pId + "</td>")
	} else{
		dw("<td class=\"navOut\" onmouseover=\"this.className='navOver';\" onmouseout=\"this.className='navOut';\" onclick=\"return objNav.goPage(" + pId + ")\">" + pId + "</td>")
	}
}

dw("<td  class=\"nextOut\" onmouseover=\"this.className='nextOver';\" onmouseout=\"this.className='nextOut';\" onclick=\"return objNav.next()\"><IMG SRC='../../image/spacer.gif' width=52 height=44></td>")
dw("</tr>");
dw("</table>");

}
/* END MAIN NAVIGATION */

// Main Navigation Object

function Navigation(intModule , intSection, intPage) {
	// Site is built of section levels, containing pages.
	// Each level has a back page, defining site structure.

	this.arrMenuText = 	
	[	// Home Page
		["Home", []],
		// Terms
		["Terms", [
							["Terms of Employment" , ["Contract" , "Holidays" , "Work Times" , "Dress Code" , "Medical Appointments" , "Special Leave" , "Sickness" , "Other Reasons for Absence" , "Absence Procedures"]],
["Test ",["Question 1", "Question 2", "Question 3", "Question 4", "Question 5", "Question 6", "Question 7", "Question 8", "Question 9", "Summary"]],
["Assessment ",["Staff handbook", "HR Policy", "Tax Form", "Identification", "Summary"]]
						]]
	];
	
	this.goModule=NAV_goModule;
	this.goSection = NAV_goSection;
	this.back = NAV_backPage;
	this.next = NAV_nextPage;
	this.home = NAV_home;
	this.goPage = NAV_goPage;
	this.doExit=exitApplication;
	this.scoURL = NAV_scoURL;
	this.scoNavigate = NAV_scoNavigate;

	this.getModuleCount = NAV_getModuleCount;
	this.getSectionCount = NAV_getSectionCount;
	this.getPageCount = NAV_getPageCount;
	
	this.module = between(0,this.getModuleCount(), intModule);
	this.section = between(0,this.getSectionCount(this.module), intSection);
	this.page = between(0,this.getPageCount(this.module, this.section), intPage);
	this.homepage = this.scoURL(0);
}

//Generate URL
function NAV_scoURL(m,s,p) {
var DEFAULTSPATH =  "defaults/pages";
var DEFAULTMODULENAME =  "module";
var DEFAULTSSECTIONNAME =  "section";

	if( isNaN(parseInt(p)) &&	 isNaN(parseInt(s))){
		if(this.getSectionCount(m) != null)
			return DEFAULTSPATH + "/" + DEFAULTMODULENAME + ".htm?mod="  + m ;//+"&sec=" + 0 + "&page=" + 1;
		else
			return MODULENAME + m + "/" + SECTIONNAME + 0 + "/" + PAGENAME + "01.htm";
	} else if(isNaN(parseInt(p)) && !isNaN(parseInt(s)) ){
		return DEFAULTSPATH + "/" + DEFAULTSSECTIONNAME + ".htm?mod=" + m +"&sec=" + s ;//+ "&page=" + 1;
	} else if(!isNaN(parseInt(s))) {
		return MODULENAME + m + "/" + SECTIONNAME + s + "/" + PAGENAME + ((p.toString().length<2)?"0":"") + p + ".htm";
	}
}
//Navigate to specified address
function NAV_scoNavigate(m,s,p) {
	if(oLMSAPI) {
		var oNode;
		if(oLMSAPI.m_oContentViewer) {//MS LRN Viewer
			oNode = oLMSAPI.m_oContentViewer.GetByResource(this.scoURL(m, s, p));
		}
		if(oNode != null)
			oLMSAPI.LRNDoSelect(oNode.getAttribute("identifier"));
		else {
			//no such item in sco manifest
		}
	} else {
		document.location.href = "../../" + this.scoURL(m, s, p);
	}
}

// Enter a MODULE (at page 1)
function NAV_goModule(intModule) {
	if(intModule > this.getModuleCount() ) intModule = this.getModuleCount();
	if(intModule < 0) intModule = 0;
	this.scoNavigate(intModule);
	//scoNavigate(intModule, 0, 1);	
	return false;
}

// Enter a section within the current module
function NAV_goSection(intSection) {
	if(intSection > this.getSectionCount(this.module)) intSection =  this.getSectionCount(this.module);
	if(intSection < 0) intSection = 0;
	this.scoNavigate(this.module, intSection);
	return false;
}

// Go to a specific Page
function NAV_goPage(intInd) {
	if(intInd > this.getPageCount(this.module, this.section)) intInd =  this.getPageCount(this.module, this.section);
	if(intInd < 1) intInd = 1;
	this.scoNavigate(this.module, this.section, intInd);
	return false;
}


// Go Back 1 page
function NAV_backPage() {
	if(oLMSAPI) {
		oLMSAPI.LRNDoPrevious();
	} else {
		var newMod;
		var newSec;
		var newPage;
		
		if (this.page == null) {
			if(this.section == null) {
				if(this.module > 0) {
					newMod = this.module - 1;
					newSec = this.getSectionCount(newMod);
					newPage = this.getPageCount(newMod, newSec);
				} else {
					newMod = this.getModuleCount();
					newSec = this.getSectionCount(newMod);
					newPage = this.getPageCount(newMod, newSec);
				}				
			} else if(this.section > 0) {
				newMod = this.module;
				newSec = this.section - 1;
				newPage = this.getPageCount(newMod, newSec);
			} else {
				newMod = this.module;
				newSec = null;
				newPage = null;
			}
		} else if (this.page > 1) {
			newMod = this.module;
			newSec = this.section;
			newPage = this.page - 1;
		} else {
			newMod = this.module;
			newSec = this.section;
			newPage = null;
		}	
		strPage ="../../"+ this.scoURL(newMod, newSec, newPage);
		
		document.location.href = strPage;
	}
	return false;
}

// Go Forward 1 page
function NAV_nextPage() {
	if(oLMSAPI) {
		oLMSAPI.LRNDoNext();
	} else {
		var newMod=	this.module;
		var newSec=	this.section;
		var newPage= this.page;

		if(this.section == null) {
			if(this.getSectionCount(this.module) != null) {
				newPage = null;
				newSec = 0;
				newMod = this.module;
			} else {				
				if (this.module == this.getModuleCount()){
					newPage = null;
					newSec = null;
					newMod = 0;
				} else {
					newPage = null;
					newSec = null;
					newMod = this.module + 1;
				}
			}
		} else if(this.page == null) {
			newPage = 1;
			newSec = this.section;
			newMod = this.module;
		} else {			
			
			if (this.page == this.getPageCount(this.module, this.section)) {
				if (this.section == this.getSectionCount(this.module)){
					newPage = null;
					newSec = null;
					if (this.module == this.getModuleCount())	{
						newMod =0;
					} else {
						newMod = this.module + 1;
					}
				
				} else {
					newPage = null;
					newSec = this.section + 1;
					newMod = this.module;
				}
			} else {
				newPage = this.page + 1;
				newSec = this.section;
				newMod = this.module;
			}
		}
		strPage ="../../"+ this.scoURL(newMod, newSec, newPage);
		document.location.href = strPage;
	}
	return false;
}

// Go to Home Page
function NAV_home() {
	if(oLMSAPI) {
		oLMSAPI.LRNDoSelectFirst();
	} else {
		document.location.href = "../../"+this.homepage;
	}
	return false;
}
// Call from Flash or Browser Exit Event
function exitApplication() {
	top.close();
}


// Get number of  MODULES
function NAV_getModuleCount() {
	if(this.arrMenuText.length > 0)
		return this.arrMenuText.length - 1;
	else
		return null;
}
// Get number of main sections
function NAV_getSectionCount(m) {
	if(this.arrMenuText[m][1].length > 0)
		return this.arrMenuText[m][1].length - 1;
	else
		return null;
}
// Get number of Pages
function NAV_getPageCount(m,s) {
	if(s == null)
		return null;
	else if (this.arrMenuText[m][1][s][1].length == 0)
		return null;
	else
		return this.arrMenuText[m][1][s][1].length;
}
