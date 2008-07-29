//--- Menu Functions ---//
var MODULENAME = "Module";
var SECTIONNAME = "Section";
var PAGENAME = "page";
var DEFAULTSPATH =  "defaults/pages";
var DEFAULTMODULENAME =  "module";
var DEFAULTSSECTIONNAME =  "section";

if(top.API) {
	var use_SimpleNav = top.use_SimpleNav;
} else {
	var use_SimpleNav = 0;
}

// Write Flash Navigation Menu
/* TOP NAVIGATION */
function writeTopMenu() {
var ThisPageName
	//ak o page-a ne ev manifesta i trite she sa null i she ima iako gurmeji i za tva izliza
	//alert('writetopmenu '+objNav.page+' ' +objNav.section+' '+objNav.module);
	
	var query = location.href; 

	var pairs = query.split("/"); 
	var str;
	if(pairs.length>0)
	{
		str=pairs[pairs.length-1];
	}
	
	
	dw("<table  width=778 cellspacing=0 cellpadding=0 border=0><tr>");
	dw("<td  width=\"100%\" style=\"text-align:center;\"  class=\"mainNavOver\" ><a  href=\"#\"  class=\"topNavActive\">Templates</a></td>" );
	dw("</tr></table>");

	dw("<div  class=\"subnav\"  >");
	dw("<a href=\"#\"  class=\"topNavActive\"></a> ");
	dw("</div>");
	dw("<div class=\"subtitle\" align=\"center\">"+str+"</div>");
	return;
	
}
/* END TOP NAVIGATION */

/* MAIN NAVIGATION */
function writeMainMenu()
 {
	dw("<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"783\" >")
	dw("<tr>")
	dw("<td class=\"navOut\" width=100% style=\"text-align:left;cursor:default;padding-left:10px;white-space:normal;\">&nbsp;</td>")

	
	dw("<td  class=\"nextOut\" onmouseover=\"this.className='nextOver';\" onmouseout=\"this.className='nextOut';\" ></td>")
	dw("</tr>");
	dw("</table>");


}
/* END MAIN NAVIGATION */

// Main Navigation Object

function Navigation(intModule , intSection, intPage) 
{
	// Site is built of section levels, containing pages.
	// Each level has a back page, defining site structure.
//alert('navigation');
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
	//alert('predi');
	this.NavigationByHref=GetNavigationByHref(location.href);
	
	this.getModuleCount = NAV_getModuleCount;
	this.getSectionCount = NAV_getSectionCount;
	this.getPageCount = NAV_getPageCount;
	//alert('predi this');
	//alert(this.arrLevels);
	this.module =between(0,this.getModuleCount(), this.arrLevels[0]);
	this.section = between(0,this.getSectionCount(this.module), this.arrLevels[1]);
	this.page = between(0,this.getPageCount(this.module, this.section), this.arrLevels[2]);
	this.homepage = this.scoURL(0);
	//alert(this.module+' '+this.section+' '+ this.page);
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
	//alert (m+' '+s+' '+p);
	
	//alert('mod '+m+' sec '+s+' page '+p);
	if( isNaN(parseInt(p)) &&	 isNaN(parseInt(s)))
	{
			if(isNaN(parseInt(m)) )
			{
				//alert('nema ia');
				return;
			}
			else if(this.getSectionCount(m) != null)	
			{		//alert('secs null');
				return DEFAULTSPATH + "/" + DEFAULTMODULENAME + ".htm?mod="  + m ;
			}
			else
			{
			//vzima href-a  na modula
		
				identifierref=courseNode.childNodes[m].getAttribute('identifierref');
				//alert(identifierref);
			}
			
	} 
	else if(isNaN(parseInt(p)) && !isNaN(parseInt(s)) )
	{
	//alert('edno');
	//alert(s);
		return DEFAULTSPATH + "/" + DEFAULTSSECTIONNAME + ".htm?mod=" + m +"&sec=" + s ;//+ "&page=" + 1;
	}
	else if(!isNaN(parseInt(s))) 
	{	
		//alert(courseNode.childNodes.length);
		//alert(courseNode.childNodes[m].childNodes[s+1].childNodes.length);
		identifierref=courseNode.childNodes[m].childNodes[s+1].childNodes[p+1].getAttribute('identifierref');
	}		
		//alert(identifierref);
	resources=xmlDoc.getElementsByTagName('resources')[0];
	for (i=0;i<resources.childNodes.length;i++)
	{
	    tmpResNode=resources.childNodes[i];
	    if (tmpResNode.getAttribute('identifier')==identifierref )
	    {	
			//var	s=	tmpResNode.getAttribute('href').replace("&amp;","&");
			//alert('href= '+s);
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

return;


	if(oLMSAPI) 	
	{
		oLMSAPI.LRNDoPrevious();
	} 
	else 
	{
		var newMod;
		var newSec;
		var newPage;
		//alert(this.page);
		
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
					//alert(newPage);
					
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
			{//alert('edno');
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
	
	
	return;
	
	
	
	
	
	if(oLMSAPI) 
	{
		oLMSAPI.LRNDoNext();
	} 
	else 
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
		{//alert('edno');
			if(this.getPageCount(this.module, this.section)>0)
			{//alert('>0');
				newPage = 0;
				newSec = this.section;
				newMod = this.module;
			}
			else if(this.section < this.getSectionCount(this.module))
			{//alert('=0');
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
		{		//alert('dve');	
			//alert('page');
			//alert(this.page );
			//alert(this.getPageCount(this.module, this.section));
			if (this.page+1 == this.getPageCount(this.module, this.section))
			{//alert('end page');
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
			{//alert(this.page+' '+this.section+' '+this.module);
				newPage = this.page +1;
				newSec = this.section;
				newMod = this.module;
			}
		}
		strPage ="../../"+ this.scoURL(newMod, newSec, newPage);
		//alert(strPage);
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
	if( isNaN(parseInt(m)))
	{
		return null;
	}
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
    {//alert('loadxml');
        var arrCourse = new Array();        
	    xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
	    xmlDoc.async=false;
	    xmlDoc.load("../../imsmanifest.xml");
		
	    courseNode=xmlDoc.getElementsByTagName('organization')[0];
	    
	    
	    //start course
	     
	    for (i=0;i<courseNode.childNodes.length;i++)
	    {	 
	        //alert('i'+i);
	        var arrModule= new Array();	
	        if (courseNode.childNodes[i].nodeName=="item")
	        {	
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
	                //alert('j'+j);	               
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
	                        //alert('add section '+arrSectionRoot.length );	
	                        
	                        	                                
	                    }
	                }
	               
	                  
	                           
	            }	             
	             //end modul
	             arrModule.push(arrSectionRoot); 
	             //alert(arrSectionRoot);
	            // alert(arrModule); 
	            // alert(arrModule[1].length); 
	             //alert(arrSection);
	            // alert(arrSection.length );
	           
	            
	         }    
	     
	        arrCourse.push(arrModule);            
	        
	    }	   
	    
	    //alert(arrCourse);
	     //end course
	    
	   return arrCourse;
    }
	
	function GetNavigationByHref(href)
	{
		//vrushta  module section i page po href-a na page-a
		var arrLevels= new Array();  
		href=href.replace("\\","/");
		//alert('GetNavigationByHref');
		var xmlDoc    = new ActiveXObject("Msxml2.DOMDocument.3.0");
		xmlDoc.async=false;
	    xmlDoc.load("../../imsmanifest.xml");
		var resNodes = xmlDoc.documentElement.selectNodes("//resource");
		//alert(resNodes.length);
		var identifierref="";
		for (i=0;i<resNodes.length;i++)
		{
			//za vseki sluchai maha sichki \ i gi zamenia s /  stoto browsera go pravi i moje da ne se nameri suvpadenieto
			var tmpHref=resNodes[i].getAttribute("href");
			tmpHref=tmpHref.replace("\\","/");
			//alert(tmpIdRef);
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
			//alert('nema go');
			arrLevels.push(null);
			arrLevels.push(null);
			arrLevels.push(null);
			//alert('null null null');
			//this.module=null;
			//this.section=null;
			//this.page=null;
			return arrLevels;
		
		}
		//alert('pochva se');
		var itemNode=xmlDoc.documentElement.selectSingleNode("//item[@identifierref='" + identifierref + "']"); 
		//alert(identifierref);
		//alert(itemNode.xml);
		var parNode=itemNode.parentNode;
		var parParNode=parNode.parentNode;
		
		var moduleIndex;
		var sectionIndex;
		var pageIndex;
		//alert(parNode.nodeName);
		
		if(parNode.nodeName!="item")
		{
			//znachi itemnode e module i namirame indeksa na modul-a - section i page sa null
			var modules=parNode.selectNodes("item");
			//alert('modules '+modules.length);
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
			//alert('moduleindex null null');
			//this.module=moduleIndex;
			//this.section=null;
			//this.page=null;
			//alert('this.module '+this.module);
		}
		else if(parParNode.nodeName!="item")
		{
			//znachi item node e section - page she ostane null
			var modules=parParNode.selectNodes("item");//parParnode e tuka organization
			//alert('modules '+modules.length);
			for (j=0;j<modules.length;j++)
			{
				if(modules(j)==parNode)
				{
					moduleIndex=j; //tva e modula i posle navlizame da mu izciklime i sekciite					
					break;
				}
			}	


			var sections=parNode.selectNodes("item");//parNode tuka e module
			//alert(sections.length);
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
			//alert('moduleindex sectionindex null');
			//this.module=moduleIndex;
			//this.section=sectionIndex;
			//this.page=null;			
			
		}
		else
		{
			//znachi e page
			//alert('vliza v page');
			var modules=parParNode.parentNode.selectNodes("item");//parParnode.parentNode e tuka organization
			//alert('modules '+modules.length);
			for (j=0;j<modules.length;j++)
			{
				if(modules(j)==parParNode)
				{
					moduleIndex=j; //tva e modula i posle navlizame da mu izciklime i sekciite					
					break;
				}
			}	
			//alert(moduleIndex);

			var sections=parParNode.selectNodes("item");//parParNode tuka e module
			//alert(sections.length);
			for(k=0;k<sections.length;k++)
			{
				if(sections[k]==parNode)
				{
					sectionIndex=k;
					break;
				}
			}
			//alert(sectionIndex);
			
			var pages=parNode.selectNodes("item");//parNode tuka e section
			//alert(sections.length);
			for(l=0;l<pages.length;l++)
			{
				if(pages[l]==itemNode)
				{
					pageIndex=l;
					break;
				}
			}
			//alert(pageIndex);
			//alert(moduleIndex+' '+sectionIndex+' '+pageIndex);
			
			arrLevels.push(moduleIndex);
			arrLevels.push(sectionIndex);
			arrLevels.push(pageIndex);
			return arrLevels;
			//alert('moduleindex sectionindex pageindex');
			//this.module=moduleIndex;
			//this.section=sectionIndex;
			//this.page=pageIndex;		
			
		}
		
		
	}
	
	