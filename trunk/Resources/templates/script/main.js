screenHeight = 416;
screenWidth = 780;

window.attachEvent("onload", initPage);
window.attachEvent("onresize", placeContent);

//var objNav = new Navigation(0,0,0);
var rightAnswer=-1;//vernia otgovor vsluchai na quiz

//alert(objNav.section);
// Called on body_onLoad

function initPage()
 {

	placeContent();
	loadPage();
	//alert("rightAnswer = " +rightAnswer);
	//alert(GetRightAnswer(objNav.module,objNav.section,objNav.page));
	//alert(objNav.section+' '+objNav.page);
	rightAnswer=GetRightAnswer(objNav.module,objNav.section,objNav.page);
	
}

//id, function, argument
function functionFlash(flashID, flashFunct, flashArg)
{
	try
	{
		return eval(flashID + "." + flashFunct + "(" + flashArg + ")");
	}
	catch(e){}
		
}

//--- Utility Functions ---//
function dw(strContent) {
	document.write(strContent);
}

//--- Place content in centre of page ---//

function placeContent () {
	intWinWidth = document.body.clientWidth;
	intWinHeight = document.body.clientHeight;
	
	intLeft = (intWinWidth/2) - (screenWidth  /2);
	intTop = (intWinHeight/2) - (screenHeight /2);
	
	document.all.maindiv.style.pixelLeft = intLeft;
	document.all.maindiv.style.pixelTop = intTop;
	document.all.maindiv.style.visibility = "visible";
}

//--- Show feedback div hiding any previous ---//

var completed = false; 
function showFeedback(i) {
	var p = 0;
	while((p < i) || (document.all("fb" + p) != null)) {
		if(document.all("fb" + p) && (p != i)) { // && (document.all("fb" + p).length == null)
			document.all("fb" + p).style.display = "none";
		}
		p++;
	}
	if(document.all("fb" + i)) {
		document.all("fb" + i).style.display = "";
	}
	setIncompleteStatus();
}

//--- Show flash div hiding any previous ---//
	
function showFlash(i) {
	if(document.all("fd" + i)) {
		var p = 1;
		while((p < i) || (document.all("fd" + p) != null)) {
			if(document.all("fd" + p) && (p != i)) {
				document.all("fd" + p).style.display = "none";
			}
			p++;
			
		}
		document.all("fd" + i).style.display = "";
	}
	setIncompleteStatus();
}

//--- Change a flash object's movie ---//

function changeFlash(fo, movie) {
	document.all(fo).Movie = movie;
}

//--- Check answer and show appropriate feedback (fb1 correct, fb2 - wrong) ---//

function showAnswer(input, answer, execStr, correctIndex) {
	if(!completed) {
		var result;
		if (!correctIndex) {
			correctIndex = 1;
		}
		result = correctIndex;//Correct by default
		var i;
		for (i=0; i < input.length; i++) {
			if((input[i]) && (input[i].checked)) {
				completed = true;
			}
			if(answer[i] != null) {
				if(!(input[i] && (input[i].checked.toString() == answer[i].toString()))) {
					result = correctIndex + 1;//Wrong answer
				}
			}
		}
		if(!completed) {
			alert("Please answer before you click submit");
		}
		else 
		{
			for (i=0; i < input.length; i++) {
				if(input[i]) {
				 input[i].disabled=true;
				}
			}
			showFeedback(result);
		    eval(execStr);
			setLessonStatus("completed");
		}
	}
}

//--- Default flash object FSCommand handler (flash object id="flash", handler: function flash<FSCommand>(<Params>))---//

function flash_DoFSCommand(command, args) {
	try {
		eval(("flash" + command + "('" + args + "')").toLowerCase());
	} catch(e) {
		//alert("Unhandled FSCommand: " + command + "(" + args + ")");
	}
}
if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && 
  navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
  document.write('<SCRIPT LANGUAGE=VBScript\> \n');
  document.write('on error resume next \n');
  document.write('Sub flash_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(command, args) end if\n');
  document.write('end sub\n');
  document.write('Sub flash1_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(1 & command, args) end if\n');
  document.write('end sub\n');
  document.write('Sub flash2_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(2 & command, args) end if\n');
  document.write('end sub\n');
  document.write('Sub flash3_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(3 & command, args) end if\n');
  document.write('end sub\n');
  document.write('Sub flash4_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(4 & command, args) end if\n');
  document.write('end sub\n');
  document.write('Sub flash5_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(5 & command, args) end if\n');
  document.write('end sub\n');
  document.write('Sub flash6_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(6 & command, args) end if\n');
  document.write('end sub\n');
  document.write('Sub flash7_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(7 & command, args) end if\n');
  document.write('end sub\n');
  document.write('Sub flash8_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(8 & command, args) end if\n');
  document.write('end sub\n');
  document.write('Sub flash9_FSCommand(ByVal command, ByVal args)\n');
  document.write('if not isEmpty(flash_DoFSCommand) then call flash_DoFSCommand(9 & command, args) end if\n');
  document.write('end sub\n');
  document.write('</SCRIPT\> \n');
} 

//--- Drag & Drop Functions ---//
var dragX = 0;
var dragY = 0;
var divX = 0;
var divY = 0;
var dragged = null;
var intDragged = 0;

document.attachEvent("onmousemove", mousemove);
document.attachEvent("onmouseup", mouseup);

function dragDiv(d) {
	if(completed) return false;
	if(!dragged) {
		dragged = d;
		intDragged = parseInt(d.id.substring(4,5));
		dragged.style.position = "absolute";
		dragged.hideFocus = true;
		
		dragX =event.clientX - event.offsetX - dragged.offsetLeft + dragged.offsetWidth/2; 
		dragY = event.clientY - event.offsetY - dragged.offsetTop + dragged.offsetHeight/2;
	 
		dragged.style.left = event.clientX- dragX;
		dragged.style.top = event.clientY - dragY;
		dragged.offsetParent.parentElement.style.zIndex = 1000;
		
	}
	return false;
}
function mousemove() {
	if(dragged) {
//	top.status = event.offsetX + " " + dragged.offsetWidth/2; 
		dragged.style.left = event.clientX  -  dragX ;
		dragged.style.top = event.clientY - dragY;
	}
	return true;
}
function mouseup() {
	if(dragged) {
		dragged.style.visibility = "hidden";
		dragDrop(document.elementFromPoint(event.clientX, event.clientY));
		dragged.offsetParent.parentElement.style.zIndex = 0;
		dragged.style.left = "";
		dragged.style.top = "";
		dragged.style.position = "static";
		dragged.style.visibility = "visible";
		dragged = null;
	}
	return true;
}
function dragDrop(target) {
	if(dragged && target) {
		switch(target.id.substring(0,4))
		{
			case "drop": //dropped onto a process slot
				putInSlot(dragged, target);
				break;
			case "boxx": //dropped onto another box
				box = document.all("text" + target.id.substring(4,5));
				if(box.parentElement.id.substring(0,4) == "drop") {//if in a slot replace
					putInSlot(dragged,box.parentElement);
				} else { //else do nothing
				}
				break;
		}
	}
	return false;
}
function putInSlot(d, slot) {
	if(slot.hasChildNodes()) {//already taken -> empty it
		document.all("init" + slot.childNodes[0].id.substring(4,5)).appendChild(slot.childNodes[0]);
	}

	/* Update Answers array */
	if(d.parentElement.id.substring(0,4) == "drop")
		Answers[d.parentElement.id.substring(4,5)] = null;
	slot.appendChild(d);
	Answers[slot.id.substring(4,5)] = new Object();
	Answers[slot.id.substring(4,5)].checked = d.id.substring(4,5);
}
 
 
 //Check to see if radio button is checked then disable it 
 
  function getQuestion(ind) 
  { 
  //alert("vliza v get questtion ");
  //alert("rightAnswer="+rightAnswer);
		var yes_Answer=rightAnswer;			//array = new Array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0); 	
		//alert("yes_Answer "+yes_Answer);
		var i=-1;
		for (j=0;j<100;j++)
		{
			//tursi radio butonite no ne se znae s kakuv index mu e id-to za tva cikli dokato nameri
			//alert(document.all("r"+j));
			if (document.all("r"+j))
			{
				if (document.all("r"+j).length >1)
				{
					i=j;
					break;
				}
			}
		}
		if(i==-1)
		{
			//ako ne sa namereni radiobutoni izliza
			return; 
		}
		
		completed=false;
		 if(!completed)
		 {	
			if(document.all("r"+i))
				if(document.all("r"+i).length > 1)
				{
					var flag=false;//pomni dali nekoi ot radioburonite e checknat
					var rbtns=document.all("r"+i);
					for (k=0;k<rbtns.length;k++)
					{
						if(rbtns[k].checked)
						{
							flag=true;
							break;
						}
					}
					if(!flag)
					{
						return;
					}				
					
					completed=true;
					for (p=0; p < document.all("r"+i).length; p++)
					{
						document.all("r"+i)[p].disabled=true;
						if((yes_Answer==p)&(document.all("r"+i)[p].checked))
						{// if yes=0 & YES is cheked then green dot
							//alert('verno');
							 setLessonStatus('passed');		
							 doLMSSetValue("cmi.core.score.raw", "1");
							 break;
						}    	 
						if((yes_Answer!=p)&(document.all("r"+i)[p].checked))
						{// if yes = 1 & YES is cheked then red dot
							//alert('greshno');
							 setLessonStatus('failed'); 
							 doLMSSetValue("cmi.core.score.raw", "0");
							 break;
						}
						/*if((yes_Answer==1)&(document.all("r"+i)[1].checked))
						{// if yes=1 & NO is cheked then green dot
							 setLessonStatus('passed');		
							 doLMSSetValue("cmi.core.score.raw", "1");
						}    	
						if((yes_Answer==0)&(document.all("r"+i)[1].checked))
						{// if yes=0 & NO is cheked then red dot
							 setLessonStatus('failed');		
							 doLMSSetValue("cmi.core.score.raw", "0");
						}    	*/
					}
					if(oLMSAPI) 
					{
						oLMSAPI.LRNDoNext();//Not SCORM compliant!
					} 
					else 
					{
						//alert('redirect');
						//alert(objNav.next());
						objNav.next(); // document.location.href.substring(0, document.location.href.length - 6) + (i>8?"":"0") + (i+1) + ".htm";
					}
					
		   	   }
		  }
	}
 //THis array is doing the same thing for each module - you may want to make these arrays into a single function - all "correct answers are '0'"
   //Check to see if radio button is checked then disable it 
 
  function getExamQuestion(i) 
  { 
		var yes_Answer=rightAnswer;
		
		var i=-1;
		for (j=0;j<100;j++)
		{
			//tursi radio butonite no ne se znae s kakuv index mu e id-to za tva cikli dokato nameri
			//alert(document.all("r"+j));
			if (document.all("r"+j))
			{
				if (document.all("r"+j).length >1)
				{
					i=j;
					break;
				}
			}
		}
		if(i==-1)
		{
			//ako ne sa namereni radiobutoni izliza
			return; 
		}
		
		completed=false;
		 if(!completed)
		 {	
			if(document.all("r"+i))
				if(document.all("r"+i).length > 1)
				{
					var flag=false;//pomni dali nekoi ot radioburonite e checknat
					var rbtns=document.all("r"+i);
					for (k=0;k<rbtns.length;k++)
					{
						if(rbtns[k].checked)
						{
							flag=true;
							break;
						}
					}
					if(!flag)
					{
						return;
					}				
					
					completed=true;
					for (p=0; p < document.all("r"+i).length; p++)
					{
						document.all("r"+i)[p].disabled=true;
						if((yes_Answer==p)&(document.all("r"+i)[p].checked))
						{// if yes=0 & YES is cheked then green dot
							 setLessonStatus('passed');		
							 doLMSSetValue("cmi.core.score.raw", "1");
						}    	 
						if((yes_Answer!=p)&(document.all("r"+i)[p].checked))
						{// if yes = 1 & YES is cheked then red dot
							 setLessonStatus('failed'); 
							 doLMSSetValue("cmi.core.score.raw", "0");
						}
						/*if((yes_Answer==1)&(document.all("r"+i)[1].checked))
						{// if yes=1 & NO is cheked then green dot
							 setLessonStatus('passed');		
							 doLMSSetValue("cmi.core.score.raw", "1");
						}    	
						if((yes_Answer==0)&(document.all("r"+i)[1].checked))
						{// if yes=0 & NO is cheked then red dot
							 setLessonStatus('failed');		
							 doLMSSetValue("cmi.core.score.raw", "0");
						}   */
					}
					
					if(oLMSAPI)
					{
						oLMSAPI.LRNDoNext();//Not SCORM compliant!
					}
					else
					{
						objNav.next();
						//document.location.href =document.location.href.substring(0, document.location.href.length - 6) + (i>8?"":"0") + (i+1) + ".htm";
					}
						
		   	   }
		  }
	}
// Create PopUp Object
	
	var oPopup = window.createPopup();
function showPopUp(pWidth , pHeight , pContent ){
	var pX=event.offsetX;
	var pY;
	oPopup.document.write('<link rel="stylesheet" type="text/css" href="../../style/popup1.css" />')
	oPopup.document.write(pContent);
	oPopup.show(pX, event.srcElement.offsetHeight, pWidth, pHeight , event.srcElement);
	oPopup.document.close();
}
var incompleteTimeOut = 0*1000;

function setIncompleteTimeout(){
	window.setTimeout("completed = true; setIncompleteStatus();", incompleteTimeOut);
}

function setIncompleteStatus(){
	if(getLessonStatus() == "browsed") setLessonStatus("incomplete");
}

function setCompleteStatus(){
	if (completed)
	{
		if (getLessonStatus() == "incomplete") setLessonStatus('completed');
	}
}

function flashflnavigate(Args){
	navArray = Args.split(".")
	objNav.scoNavigate(parseInt(navArray[0]), parseInt(navArray[1]), parseInt(navArray[2]));
}

function between(low, high, x) {
//alert('between '+x);
	x = parseInt(x);
	if(isNaN(x))
		return null;
	else if(x < low)
		return low;
	else if(x > high)
		return high;
	else
		return x;
}

function GetRightAnswer(moduleIndex,sectionIndex,pageIndex)
{
//alert("vliza");
//alert(moduleIndex+' * '+sectionIndex+' * '+pageIndex);
	if( isNaN(parseInt(moduleIndex)) || isNaN(parseInt(sectionIndex))||(isNaN(parseInt(pageIndex))))
	{
		return -1;
	}

	//otvaria manifesta 
	var identifier,courseNode;
	var xmlDoc    = new ActiveXObject("Msxml2.DOMDocument.3.0");
	xmlDoc.async=false;
	xmlDoc.load("../../imsmanifest.xml");
	courseNode= xmlDoc.documentElement.selectNodes("//organization")(0);
	
	//namira page item-a i vrushta identifier-a mu
	identifier=courseNode.childNodes[moduleIndex].childNodes[sectionIndex+1].childNodes[pageIndex+1].getAttribute('identifier');
	//alert(identifier);
	//otvaria quizXML faila
	var quizesNode,questionNode,answers;
	var xmlQuizDoc=new ActiveXObject("Msxml2.DOMDocument.3.0");
	xmlQuizDoc.async=false;
	xmlQuizDoc.load("../../quizXML.xml");	
	
	//izbira questiona koito ima ID kato tova na dadenia item v maniffesta
	//alert(xmlQuizDoc);
	questionNode=xmlQuizDoc.documentElement.selectNodes("//Question[@QuestionID='" + identifier + "']")[0];	
	if (!questionNode)
	{	
		return -1;
	}
	//alert(questionNode.xml);
	
	//vzima otgovorite izciklia gi i vrushta indexa na node-a koit0 e veren otgovor
	answers=questionNode.selectNodes(".//Answer");
	//alert(answers.length);
	for (k=0;k<answers.length;k++)
	{
		if(answers[k].getAttribute('Correct')=="1")
		{
			//alert("k="+k);
			return k;
		}
	}
	
	return -1;
	
}
