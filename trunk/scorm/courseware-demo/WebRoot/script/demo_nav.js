//--- Menu Functions ---//
var MODULENAME = "Module";
var SECTIONNAME = "Section";
var PAGENAME = "page";
// Write Flash Navigation Menu

/* TOP NAVIGATION */
function writeTopMenu() {
var ThisPageName= objNav.arrMenuText[objNav.module][1][objNav.section][1][objNav.page-1];
	 
	//dw("<div  class=\"navOut\" style=\"text-align:left; padding:0px 0px 1px 10px;margin:0 0 0 0;width:778px;\">");
dw("<table  width=778 cellspacing=0 cellpadding=0 border=0><tr>");
	for(modId=0;modId<objNav.getModulCount(); modId++){
	ThisModuleName= objNav.arrMenuText[modId][0];
		if(objNav.module == modId){	
			dw("<td class=\"mainNavOver\" >" + ThisModuleName + "</td>" );				
		} else{
			dw("<td class=\"mainNavOut\" ><a href=\"\" onclick=\"return objNav.goModule("+ modId + ")\" class=\"mainTopNav\">" + ThisModuleName + "</a></td>");
		}
		
		if(modId <objNav.getModulCount()-1){
		dw("<td width=2><img src=\"../../image/sp-orqnge.gif\" width=2 height=18 hspace=0></td>");		
		} else	 {
		dw("<td width=2><img src=\"../../image/sp-orqnge.gif\" width=2 height=18 hspace=0></td>");				
		dw("<td class=\"exitSt\" onclick=\"objNav.doExit();return false;\" >Exit</td>");		
		}
		
		
	}
dw("</tr></table>");	
	//dw("</div>");	


	dw("<div  class=\"subnav\" >");
	for(secId=0;secId<objNav.getSectCount(); secId++){
	ThisSectionName= objNav.arrMenuText[objNav.module][1][secId][0];
		if(secId != objNav.getSectCount()-1){
			gtElem="&gt;&nbsp;";
		} else{
			gtElem="";
		}
		if(objNav.section == secId){	
				dw("<span class=\"topNavActive\" >" + ThisSectionName + "</span> "+gtElem);				
			} else{
				dw("<a href=\"\" onclick=\"return objNav.goSection("+ secId + ")\" class=\"topNav\">" + ThisSectionName + "</a> "+gtElem);
		}
	}
dw("</div>");
dw("<div class=\"subtitle\">" + ThisPageName + "</div>");
	
	
}
/* END TOP NAVIGATION */

/* MAIN NAVIGATION */
function writeMainMenu() {
var ThisModuleName= objNav.arrMenuText[objNav.module][0];
var ThisSectionName= objNav.arrMenuText[objNav.module][1][objNav.section][0];
var ThisPageName= objNav.arrMenuText[objNav.module][1][objNav.section][1][objNav.page-1];
//	 navString=new Array(ThisModuleName , ThisSectionName , ThisPageName);
	 
dw("<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"780\" >")
dw("<tr>")
dw("<td class=\"navOut\" width=100% style=\"text-align:left;cursor:default;padding:0px 0px 0px 10px;\" height=20>"+ThisModuleName +" > "+ ThisSectionName +" > "+ ThisPageName+ "</td>")
for(pId=1;pId<objNav.getPageCount()+1; pId++){
	if(pId == (objNav.page)){
		dw("<td class=\"navOut\" onmouseover=\"this.className='navOver';\" onmouseout=\"this.className='navOut';\" onclick=\"return objNav.back()\">&nbsp;&lt; BACK&nbsp;</td>")
		dw("<td class=\"navActive\" style=\"cursor:default;\">"+ pId + "</td>")
	} else{
		dw("<td class=\"navOut\" onmouseover=\"this.className='navOver';\" onmouseout=\"this.className='navOut';\" onclick=\"return objNav.goPage(" + pId + ")\">" + pId + "</td>")
	}
}
dw("<td  class=\"navOut\" onmouseover=\"this.className='navOver';\" onmouseout=\"this.className='navOut';\" onclick=\"return objNav.next()\">&nbsp;&nbsp;NEXT &gt;&nbsp;</td>")
dw("</tr>");
dw("</table>");

}
/* END MAIN NAVIGATION */

// Main Navigation Object

function Navigation(intModule , intSection, intPage) {
	this.module = intModule;
	this.section = intSection;
	this.page = intPage;

	// Site is built of section levels, containing pages.
	// Each level has a back page, defining site structure.

	this.homepage = "../../" + MODULENAME + "0/" + SECTIONNAME + "0/" + PAGENAME + "01.htm";
	
	this.arrMenuText = 	
	[	// Home Page
		["Home", [
						["",[""]]
						]],
		// Terms
		["Terms", [
		["",[""]]
						]],
						//	["Terms of Employment" , ["Contract" , "Holidays" , "Work Times" , "Dress code" , "Medical Appointments" , "Special Leave" , "Sickness" , "Other Reasons for absence" , "Absence procedures"]]
						//]],
		// Security
		["Security", [
						["",[""]]
						]],
		// Health & Safety
		["Health & Safety", [
										["Seating", ["Seating posture", "Backrest", "Seat height", "Overview"]], 
									    ["Desk", ["Desk Surface", "Desk Height (i)", "Desk Height (ii)", "Desk Storage"]],
									    ["Keyboard", ["Keyboard Angle", "PC vs Laptop", "Mouse Position", "Mouse Operation"]],
									    ["Monitor", ["Monitor Height", "Monitor Distance"]],
									    ["Lighting", ["Task Lighting", "General Lighting"]],
									    ["Work", ["Micro Breaks", "Mini Breaks", "Short-cuts", "Specialist Tasks", "Software Performance"]],
									    ["Environment", ["Temperature", "Noise", "Health"]],
									    ["Manual Handling", ["Introduction", "Lifting techniques"]],
									    ["Fire Safety", ["Good Housekeeping", "Egress" , "Discovery of fire" ]],
									    ["Accidents",["First Aid" , "Reporting Procedures"]],
									    ["Assessment ",["Assessment"]]
									  ]],
		// Company Info
		["Company Info", [
						["",[""]]
						]],
		// Remuneration
		["Remuneration", [
						["",[""]]
						]],
		// Rules
		["Rules", [
						["",[""]]
						]],
		// Development
		["Development", [
						["",[""]]
						]],
		// Final Word
		["Final Word", [
						["",[""]]
						]]
	];
	this.goModule=NAV_goModule;
	this.goSection = NAV_goSection;
	this.back = NAV_backPage;
	this.next = NAV_nextPage;
	this.home = NAV_home;
	this.goPage = NAV_goPage;
	this.doExit=exitApplication;

	this.getModulCount = NAV_getModulCount;
	this.getSectCount = NAV_getSectCount;
	this.getPageCount = NAV_getPageCount;
}

// Enter a MODULE (at page 1)
function NAV_goModule(intModule) {
	if(intModule >this.getModulCount()-1 ) intModule = this.getModulCount()-1;
	if(intModule < 0) intModule = 0;
	document.location.href = "../../" + MODULENAME + intModule+ "/"+ SECTIONNAME + "0/" + PAGENAME + "01.htm";
	return false;
}

// Enter a section (at page 1)
function NAV_goSection(intSection) {
	if(intSection > this.getSectCount()-1) intSection =  this.getSectCount()-1;
	if(intSection < 0) intSection = 0;
	document.location.href = "../" + SECTIONNAME + intSection + "/" + PAGENAME + "01.htm";
	return false;
}
// Go to a specific Page
function NAV_goPage(intInd) {
	if(intInd > this.getPageCount) intInd =  this.getPageCount();
	if(intInd < 1) intInd = 1;
	if(intInd <=9){
		intInd = "0" + intInd;
	}
	document.location.href = "../"+ SECTIONNAME + this.section + "/"+ PAGENAME + intInd +".htm";
	return false;
}


// Go Back 1 page
function NAV_backPage() {
var newMod=	this.module;
var newSec=	this.section;
var newPage= this.page;

		if (this.page == 1) {
			if(this.module == 0){
				newMod =0;
			} else if (this.section == 0){
				newMod-=1;
			}

			if(this.section == 0){
				newSec =0;
			} else{
				newSec-=1;
			}			
			newPage=1;
		} else{
			newPage-=1;
		}
		if(newPage <=9){
			newPage = "0" + newPage;
		}
		strPage ="../../"+ MODULENAME + newMod+ "/" + SECTIONNAME + newSec + "/" + PAGENAME +  newPage  + ".htm";
		document.location.href = strPage;
	return false;
}

// Go Forward 1 page
function NAV_nextPage() {
var newMod=	this.module;
var newSec=	this.section;
var newPage= this.page;

		if (this.page == this.getPageCount()) {
				if(this.module == this.getModulCount()-1 ){
					newMod =0;
				} else if (this.section == this.getSectCount()-1){
					newMod+=1;
				}

				if(this.section == this.getSectCount()-1){
					newSec =0;
				} else{
					newSec+=1;
				}			
			newPage=1;
		} else{
			newPage+=1;
		}
		if(newPage <=9){
			newPage = "0" + newPage;
		}
		
		strPage ="../../"+ MODULENAME + newMod+ "/" + SECTIONNAME + newSec + "/" + PAGENAME +  newPage  + ".htm";
		document.location.href = strPage;
	return false;
}



// Go to Home Page
function NAV_home() {
	document.location.href = this.homepage;
	return false;
}
// Call from Flash or Browser Exit Event
function exitApplication() {
	top.close();
}


// Get number of  MODULES
function NAV_getModulCount() {
	return this.arrMenuText.length;
}
// Get number of main sections
function NAV_getSectCount() {
	return this.arrMenuText[this.module][1].length;
}
// Get number of Pages
function NAV_getPageCount() {
	return this.arrMenuText[this.module][1][this.section][1].length;
}
