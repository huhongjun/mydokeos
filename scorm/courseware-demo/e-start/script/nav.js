//--- Menu Functions ---//
var MODULENAME = "Module";
var SECTIONNAME = "Section";
var PAGENAME = "page";
var DEFAULTSPATH =  "defaults/pages";
var DEFAULTMODULENAME =  "module";
var DEFAULTSSECTIONNAME =  "section";

if(top.API) {
	var use_SimpleNav = ((!isNaN(top.use_SimpleNav))?top.use_SimpleNav:-1);
} else {
	var use_SimpleNav = 0;
}

// Write Flash Navigation Menu
/* TOP NAVIGATION */

 
function writeTopMenu() {
var ThisPageName
	if( (isNaN(objNav.page)||(objNav.page==null)) && (isNaN(objNav.section)||(objNav.section==null)) && (isNaN(objNav.module)||(objNav.module==null)))
	{		
		return;
	}
	
	
	if( isNaN(parseInt(objNav.page)) && isNaN(parseInt(objNav.section))){
		ThisPageName= objNav.arrMenuText[objNav.module][0];
	} else if(isNaN(parseInt(objNav.page)) && !isNaN(parseInt(objNav.section))){
		ThisPageName= objNav.arrMenuText[objNav.module][1][objNav.section][0];
	} else {
		ThisPageName= objNav.arrMenuText[objNav.module][1][objNav.section][1][objNav.page];
	}
	//var ThisPageName= objNav.arrMenuText[objNav.module][1][objNav.section][1][objNav.page-1];
	 
	//dw("<div  class=\"navOut\" style=\"text-align:left; padding:0px 0px 1px 10px;margin:0 0 0 0;width:778px;\">");
dw("<table  width=778 cellspacing=0 cellpadding=0 border=0><tr>");
	if (use_SimpleNav == -1){
	
			if(objNav.getModuleCount() == 0){
				dw("<td class=\"exitSt\" style=\"cursor:default !important;\">&nbsp;Home&nbsp;</td>");
				dw("<td width=2><img src=\"../../image/sp-orqnge.gif\" width=2 height=18 hspace=0></td>");		
			}

			for(modId=0;modId<=objNav.getModuleCount(); modId++){
				ThisModuleName= objNav.arrMenuText[modId][0];
				if(objNav.module == modId){	
					dw("<td class=\"mainNavOver\" ><span class=\"topNavActive\">&nbsp;" + ThisModuleName + "</span></td>" );
				} else{
					dw("<td class=\"mainNavOut\" ><span class=\"mainTopNav\">&nbsp;" + ThisModuleName + "</span></td>");
				}
				if(objNav.getModuleCount() != modId){
					dw("<td width=1><img src=\"../../image/sp-orqnge.gif\" width=2 height=18 hspace=0></td>");		
				}
			}
			dw("</tr></table>");	

			dw("<div  class=\"subnav\" >");
			for(secId=0;objNav.getSectionCount(objNav.module) != null && objNav.getSectionCount(objNav.module) >= secId; secId++){
				ThisSectionName= objNav.arrMenuText[objNav.module][1][secId][0];
				if(secId != objNav.getSectionCount(objNav.module)){
					gtElem="&gt;&nbsp;";
				} else{
					gtElem="";
				}
				if(objNav.section == secId){	
						dw("<span class=\"topNavActive\">" + ThisSectionName + "</span> "+gtElem);
					} else{
						dw("<span class=\"topNav\">" + ThisSectionName + "</span> "+gtElem);
				}
			}
	
	} else	if (use_SimpleNav != 1){
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
	} 
	else  {
			if(objNav.getModuleCount() == 0){
				dw("<td class=\"exitSt\" onclick=\"return objNav.home()\">&nbsp;Home&nbsp;</td>");
				dw("<td width=2><img src=\"../../image/sp-orqnge.gif\" width=2 height=18 hspace=0></td>");		
			}

			for(modId=0;modId<=objNav.getModuleCount(); modId++){
			ThisModuleName= objNav.arrMenuText[modId][0];
				if(objNav.module == modId){	
					dw("<td class=\"mainNavOver\" ><span class=\"topNavActive\">" + ThisModuleName + "</span></td>" );
				} else{
					dw("<td class=\"mainNavOut\" ><span class=\"mainTopNav\">" + ThisModuleName + "</span></td>");
				}
				dw("<td width=2><img src=\"../../image/sp-orqnge.gif\" width=2 height=18 hspace=0></td>");		
			}
			dw("<td class=\"exitSt\" onclick=\"objNav.doExit();return false;\" >Exit</td>");			
			dw("</tr></table>");	

			dw("<div  class=\"subnav\" >");
			for(secId=0;objNav.getSectionCount(objNav.module) != null && objNav.getSectionCount(objNav.module) >= secId; secId++){
				ThisSectionName= objNav.arrMenuText[objNav.module][1][secId][0];
				if(secId != objNav.getSectionCount(objNav.module)){
					gtElem="&gt;&nbsp;";
				} else{
					gtElem="";
				}
				if(objNav.section == secId){	
						dw("<span class=\"topNavActive\" >" + ThisSectionName + "</span> "+gtElem);
					} else{
						dw("<span class=\"topNav\">" + ThisSectionName + "</span> "+gtElem);
				}
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

if( (isNaN(objNav.page)||(objNav.page==null)) && (isNaN(objNav.section)||(objNav.section==null)) && (isNaN(objNav.module)||(objNav.module==null)))
	{		
		return;
	}
	

if( isNaN(parseInt(objNav.page)) && isNaN(parseInt(objNav.section))){
	ThisModuleName = objNav.arrMenuText[objNav.module][0];
	var navStringArray = new Array(ThisModuleName);
} else if(isNaN(parseInt(objNav.page)) && !isNaN(parseInt(objNav.section))){
	ThisModuleName = objNav.arrMenuText[objNav.module][0];
	ThisSectionName = objNav.arrMenuText[objNav.module][1][objNav.section][0];
	
	var navStringArray = new Array(ThisModuleName, ThisSectionName);
} else {
	ThisModuleName = objNav.arrMenuText[objNav.module][0];
	ThisSectionName = objNav.arrMenuText[objNav.module][1][objNav.section][0];
	ThisPageName = objNav.arrMenuText[objNav.module][1][objNav.section][1][objNav.page];

	var navStringArray = new Array(ThisModuleName, ThisSectionName, ThisPageName);
}
if(objNav.getModuleCount() == 0){	navStringArray.shift();}
navString = navStringArray.join(" &gt; ");
	if (use_SimpleNav == -1){
	
			dw("<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"780\" >")
			dw("<tr>")
			dw("<td class=\"navOut\" width=100% style=\"text-align:left;cursor:default;padding-left:10px;white-space:normal;height:40px;\">"+ navString + "&nbsp;</td>")

			
			for(pId=0;pId<objNav.getPageCount(objNav.module, objNav.section); pId++){
				if(pId == (objNav.page)){
					dw("<td class=\"navOver\" style=\"cursor:default;\">"+ (pId+1) + "</td>")
				} else{
					dw("<td class=\"navOut\" style=\"cursor:default;\">" + (pId+1) + "</td>")
				}
			}

			//dw("<td  class=\"nextOut\" onmouseover=\"this.className='nextOver';\" onmouseout=\"this.className='nextOut';\" onclick=\"return objNav.next()\"><IMG SRC='../../image/spacer.gif' width=52 height=44></td>")
			dw("</tr>");
			dw("</table>");
	
	} else if (use_SimpleNav != 1){
			dw("<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"780\" >")
			dw("<tr>")
			dw("<td class=\"navOut\" width=100% style=\"text-align:left;cursor:default;padding-left:10px;white-space:normal;\">"+ navString + "&nbsp;</td>")

			if( (isNaN(parseInt(objNav.page)) && !objNav.section) || (isNaN(parseInt(objNav.page)) && objNav.section)){
				dw("<td class=\"backOut\" onmouseover=\"this.className='backOver';\" onmouseout=\"this.className='backOut';\" onclick=\"return objNav.back()\"><IMG SRC='../../image/spacer.gif' width=52 height=44></td>")
			}
			for(pId=0;pId<objNav.getPageCount(objNav.module, objNav.section); pId++){
				if(pId == (objNav.page)){
					dw("<td class=\"backOut\" onmouseover=\"this.className='backOver';\" onmouseout=\"this.className='backOut';\" onclick=\"return objNav.back()\"><IMG SRC='../../image/spacer.gif' width=52 height=44></td>")
					dw("<td class=\"navOver\" style=\"cursor:default;\">"+ (pId+1) + "</td>")
				} else{
					dw("<td class=\"navOut\" onmouseover=\"this.className='navOver';\" onmouseout=\"this.className='navOut';\" onclick=\"return objNav.goPage(" + (pId+1) + ")\">" + (pId+1) + "</td>")
				}
			}

			dw("<td  class=\"nextOut\" onmouseover=\"this.className='nextOver';\" onmouseout=\"this.className='nextOut';\" onclick=\"return objNav.next()\"><IMG SRC='../../image/spacer.gif' width=52 height=44></td>")
			dw("</tr>");
			dw("</table>");
	} else {
			dw("<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"780\" >")
			dw("<tr>")
			if( (!objNav.page && !objNav.section) || (!objNav.page && objNav.section)){
				dw("<td><button  class=\"OutButton\" onmouseover=\"this.className='OverButton';\" onmouseout=\"this.className='OutButton';\" onclick=\"return objNav.back()\" tabindex=\"1001\" title=\"Back to previous screen\">&lt; Back</button></td>")
			}
			for(pId=1;pId<=objNav.getPageCount(objNav.module, objNav.section); pId++){
				if(pId == (objNav.page)){
					dw("<td><button  class=\"OutButton\" onmouseover=\"this.className='OverButton';\" onmouseout=\"this.className='OutButton';\" onclick=\"return objNav.back()\" tabindex=\"1001\" title=\"Back to previous screen\">&lt; Back</button></td>")
				}
			}
			dw("<td class=\"navOut\" style=\"width:100%;text-align:left;cursor:default;padding-left:10px;padding-top:15px;white-space:normal;\">"+ navString + "&nbsp;</td>");
			dw("<td><button class=\"OutButton\" onmouseover=\"this.className='OverButton';\" onmouseout=\"this.className='OutButton';\" onclick=\"return objNav.next()\" tabindex=\"1002\" title=\"Next Screen\">Next &gt;</button></td>");
			dw("</tr>");
			dw("</table>");	
	}

}
/* END MAIN NAVIGATION */

// Main Navigation Object

function Navigation(intModule , intSection, intPage) 
{
	// Site is built of section levels, containing pages.
	// Each level has a back page, defining site structure.

	this.arrMenuText = loadXML();	

	this.arrLevels= GetNavigationByHref(location.href);
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
	
	this.module =between(0,this.getModuleCount(), this.arrLevels[0]);
	this.section = between(0,this.getSectionCount(this.module), this.arrLevels[1]);
	this.page = between(0,this.getPageCount(this.module, this.section), this.arrLevels[2]);
	this.homepage = this.scoURL(0);
	
}

//Generate URL
function NAV_scoURL(m,s,p)
    {
   
    var DEFAULTSPATH =  "defaults/pages";
    var DEFAULTMODULENAME =  "module";
    var DEFAULTSSECTIONNAME =  "section";
    var identifierref="";
    var resources;
    var tmpResNode;
    
    var xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
	xmlDoc.async=false;
	xmlDoc.load("../../imsmanifest.xml");	
	courseNode=xmlDoc.getElementsByTagName('organization')[0];	
	
	rootItemNodes = courseNode.selectNodes("item");
	
	if( isNaN(parseInt(p)) &&	 isNaN(parseInt(s)))
	{
			if(isNaN(parseInt(m)) )
			{				
				return;
			}
			else if(this.getSectionCount(m) != null)	
			{		
				return DEFAULTSPATH + "/" + DEFAULTMODULENAME + ".htm?mod="  + m ;
			}
			else
			{
			//vzima href-a  na modula
				//EVTIM
				//identifierref=courseNode.childNodes[m].getAttribute('identifierref');
				///DImo
				identifierref=rootItemNodes[m].getAttribute('identifierref');
			}
			
	} 
	else if(isNaN(parseInt(p)) && !isNaN(parseInt(s)) )
	{
	
		return DEFAULTSPATH + "/" + DEFAULTSSECTIONNAME + ".htm?mod=" + m +"&sec=" + s ;//+ "&page=" + 1;
	}
	else if(!isNaN(parseInt(s))) 
	{	
		//Evtim
		//identifierref=courseNode.childNodes[m].childNodes[s+1].childNodes[p+1].getAttribute('identifierref');
		//Dimo
		identifierref=rootItemNodes[m].childNodes[s+1].childNodes[p+1].getAttribute('identifierref');
	}		
		
	resources=xmlDoc.getElementsByTagName('resources')[0];
	for (i=0;i<resources.childNodes.length;i++)
	{
	    tmpResNode=resources.childNodes[i];
	    if (tmpResNode.getAttribute('identifier')==identifierref )
	    {	
			
	        return tmpResNode.getAttribute('href');
	    }
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
function NAV_goPage(intInd) 
{
	intInd=intInd-1;
	if(intInd > this.getPageCount(this.module, this.section)) intInd =  this.getPageCount(this.module, this.section);
	if(intInd < 1) intInd = 0;
	this.scoNavigate(this.module, this.section, intInd);
	return false;
}


// Go Back 1 page
function NAV_backPage() 
{
	if(oLMSAPI) 	
	{
		oLMSAPI.LRNDoPrevious();
	} 
	else 
	{
		var newMod;
		var newSec;
		var newPage;
		
		
		if (this.page == null) 
		{
			if(this.section == null) 
			{
				if(this.module > 0)
				{				
					newMod = this.module - 1;					
					newSec = this.getSectionCount(newMod);
					newPage = this.getPageCount(newMod, newSec);
					newPage=(newPage>0)?newPage-1:newPage;
					
					
				} 
				else 
				{
				
					newMod = this.getModuleCount();
					newSec = this.getSectionCount(newMod);
					newPage = this.getPageCount(newMod, newSec);
					newPage=(newPage>0)?newPage-1:newPage;
					
				}				
			} 
			else if(this.section > 0) 
			{
				newMod = this.module;
				newSec = this.section - 1;
				if(this.getPageCount(newMod, newSec)>0)
				{
					newPage = this.getPageCount(newMod, newSec)-1;
				}
				else
				{
					newPage=null;
				}
			} 
			else 
			{
				newMod = this.module;
				newSec = null;
				newPage = null;
			}
		}
		else if (this.page > 0) 
		{
			newMod = this.module;
			newSec = this.section;
			newPage = this.page -1;
		} 
		else 
		{
			newMod = this.module;
			newSec = this.section;
			newPage =null;
		}	
		strPage ="../../"+ this.scoURL(newMod, newSec, newPage);
		
		document.location.href = strPage;
	}
	return false;
}

// Go Forward 1 page
function NAV_nextPage() 
{//alert('vliza');

	if(oLMSAPI && oLMSAPI.LRNDoNext) 
	{
		oLMSAPI.LRNDoNext();
	} 
	else if(! oLMSAPI)
	{
		var newMod=	this.module;
		var newSec=	this.section;
		var newPage= this.page;

		if(this.section == null)
		{
			if(this.getSectionCount(this.module) != null) 
			{
				newPage = null;
				newSec = 0;
				newMod = this.module;
			} 
			else
			{				
				if (this.module == this.getModuleCount())
				{
					newPage = null;
					newSec = null;
					newMod = 0;
				} 
				else 
				{
					newPage = null;
					newSec = null;
					newMod = this.module + 1;
				}
			}
		}
		else if(this.page == null) 
		{
			if(this.getPageCount(this.module, this.section)>0)
			{
				newPage = 0;
				newSec = this.section;
				newMod = this.module;
			}
			else if(this.section < this.getSectionCount(this.module))
			{
				newPage = null;
				newSec = this.section+1;
				newMod = this.module;
			}
			else if(this.module==this.getModuleCount())
			{
				newPage=null;
				newSec=null;
				newMod=0;				
			}
			else
			{
				newPage=null;
				newSec=null;
				newMod=this.module+1;	
				
			}
			
			
		} 
		else 
		{			
			
			if (this.page+1 == this.getPageCount(this.module, this.section))
			{
				if (this.section == this.getSectionCount(this.module))
				{
					newPage = null;
					newSec = null;
					if (this.module == this.getModuleCount())	
					{
						newMod =0;
					}
					else 
					{
						newMod = this.module + 1;
					}
				
				} 
				else 
				{
					newPage = null;
					newSec = this.section + 1;
					newMod = this.module;
				}
			}
			else 
			{
				newPage = this.page +1;
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

function loadXML()
    {
        var arrCourse = new Array();        
	    xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
	    xmlDoc.async=false;
	    xmlDoc.load("../../imsmanifest.xml");
		
	    courseNode = xmlDoc.getElementsByTagName('organization')[0];
	    
	    //start course
	    
	    for (i=0;i<courseNode.childNodes.length;i++)
	    {	 
	        
	        
	        //Evtim
	        //var arrModule= new Array();	
	        if (courseNode.childNodes[i].nodeName=="item")
	        {	
				//Dimo
				var arrModule= new Array();	
	         
	            //izcikliat se modulite	            
	           moduleNode=courseNode.childNodes[i];
	           
	            for (x=0;x<moduleNode.childNodes.length;x++)
	            {	
	                //namira title-a	i go vkarva                           
	                if (moduleNode.childNodes[x].nodeName=='title')
	                {	    
	               
	                    arrModule.push(moduleNode.childNodes[x].text);
	                    break;
	                }
	            }  
	              
	            //start section array
	            var arrSection = new Array();
	            var arrSectionRoot = new Array();
	            if (moduleNode.childNodes.length>1)
	            {       
	                
	                
	                for (j=0;j<moduleNode.childNodes.length;j++)
	                {	
	                               
	                 if (moduleNode.childNodes[j].nodeName=='item')
	                    {
	                        //izcikliat se section node-ovete
	                         //start section
	                        sectionNode=moduleNode.childNodes[j];    	                    
    	                    
	                        for (y=0;y<sectionNode.childNodes.length;y++)
	                        {	
	                            //namira title-a	i go vkarva                           
	                            if (sectionNode.childNodes[y].nodeName=='title')
	                            {
	                            
	                               arrSection.push(sectionNode.childNodes[y].text);
	                               break;
	                            }
	                        }    
    	                    
	                         //start pages array
	                        var arrPage=null; 
	                        arrPage = new Array();
	                        if(sectionNode.childNodes.length>1)
	                        {
						        for (k=0;k<sectionNode.childNodes.length;k++)
						        {	  
						        //alert('k'+k);  
							        if (sectionNode.childNodes[k].nodeName=='item')
							        {	                        
								        //izcikliat se page-ovete								        
								        pageNode=sectionNode.childNodes[k];								        
    								
								        for (z=0;z<pageNode.childNodes.length;z++)
								        {	
									        //namira title-a	i go vkarva                           
									        if (pageNode.childNodes[z].nodeName=='title')
									        {
										        arrPage.push(pageNode.childNodes[z].text);
										        break;
									        }
								        }								        
							        }
						        }	
						        					       
						    }	                         
	                         //end section	
	                        arrSection.push(arrPage); 
	                        arrSectionRoot.push(arrSection );
							arrSection=new Array(); //da go zachisti
	                                                
	                        	                                
	                    }
	                }
	               
	                  
	                           
	            }	             
	             //end modul
  	            arrModule.push(arrSectionRoot); 
				arrCourse.push(arrModule);
	         }    
			
	    }	   
	    

	     //end course
	   return arrCourse;
    }
	
	function GetNavigationByHref(href)
	{
		//vrushta  module section i page po href-a na page-a
		
		var arrLevels= new Array();  
		href=href.replace("\\","/");
		
		var xmlDoc    = new ActiveXObject("Msxml2.DOMDocument.3.0");
		xmlDoc.async=false;
	    xmlDoc.load("../../imsmanifest.xml");
		var resNodes = xmlDoc.documentElement.selectNodes("//resource");
		
		var identifierref="";
		for (i=0;i<resNodes.length;i++)
		{
			//za vseki sluchai maha sichki \ i gi zamenia s /  stoto browsera go pravi i moje da ne se nameri suvpadenieto
			var tmpHref=resNodes[i].getAttribute("href");
			tmpHref=tmpHref.replace("\\","/");
			
			if(  href.indexOf(tmpHref) >-1)
			{
				//ako go ima v href-a znachi che tva e tursenia resource node ,getvame mu identifiera
				identifierref=resNodes[i].getAttribute("identifier");
			}			
		}
		//TODO ako identifierref="" znachi go nema v manifesta -> 3 null
		
		if(identifierref=="")
		{	
			//nema go v manifesta
			
			arrLevels.push(null);
			arrLevels.push(null);
			arrLevels.push(null);
			
			return arrLevels;
		
		}
		
		var itemNode=xmlDoc.documentElement.selectSingleNode("//item[@identifierref='" + identifierref + "']"); 		
		var parNode=itemNode.parentNode;
		var parParNode=parNode.parentNode;
		
		var moduleIndex;
		var sectionIndex;
		var pageIndex;
		
		
		if(parNode.nodeName!="item")
		{
			//znachi itemnode e module i namirame indeksa na modul-a - section i page sa null
			var modules=parNode.selectNodes("item");
			
			for (j=0;j<modules.length;j++)
			{
				if(modules(j)==itemNode)
				{
					moduleIndex=j;
					break;
				}
			}
			
			arrLevels.push(moduleIndex);
			arrLevels.push(null);
			arrLevels.push(null);
			return arrLevels;
			
		}
		else if(parParNode.nodeName!="item")
		{
			//znachi item node e section - page she ostane null
			var modules=parParNode.selectNodes("item");//parParnode e tuka organization
			
			for (j=0;j<modules.length;j++)
			{
				if(modules(j)==parNode)
				{
					moduleIndex=j; //tva e modula i posle navlizame da mu izciklime i sekciite					
					break;
				}
			}	


			var sections=parNode.selectNodes("item");//parNode tuka e module
			
			for(k=0;k<sections.length;k++)
			{
				if(sections[k]==itemNode)
				{
					sectionIndex=k;
					break;
				}
			}
			
			arrLevels.push(moduleIndex);
			arrLevels.push(sectionIndex);
			arrLevels.push(null);
			return arrLevels;
				
			
		}
		else
		{
			//znachi e page
			
			var modules=parParNode.parentNode.selectNodes("item");//parParnode.parentNode e tuka organization
			
			for (j=0;j<modules.length;j++)
			{
				if(modules(j)==parParNode)
				{
					moduleIndex=j; //tva e modula i posle navlizame da mu izciklime i sekciite					
					break;
				}
			}	
			

			var sections=parParNode.selectNodes("item");//parParNode tuka e module
			
			for(k=0;k<sections.length;k++)
			{
				if(sections[k]==parNode)
				{
					sectionIndex=k;
					break;
				}
			}
			
			
			var pages=parNode.selectNodes("item");//parNode tuka e section
			
			for(l=0;l<pages.length;l++)
			{
				if(pages[l]==itemNode)
				{
					pageIndex=l;
					break;
				}
			}
						
			arrLevels.push(moduleIndex);
			arrLevels.push(sectionIndex);
			arrLevels.push(pageIndex);
			return arrLevels;
			
			
		}
		
		
	}
