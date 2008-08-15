//--- Push Button Object ---//

// strImageName - NAME property of IMG tag
// strAction - Code to eval on click
// flgInitState - initial button state (active/inactive)

// Button Naming Convention: _0 - Disabled; _1 - Normal; _2 - Rollover

function PushButton(strImgName, strAction, flgInitActiveState) {
	
	this.strButObj = "document." + strImgName;
	
	this.action = strAction;
	this.curState = flgInitActiveState;
	
	this.rollOn = BTN_rollOn;
	this.rollOff = BTN_rollOff;
	this.click = BTN_click;
	this.enable = BTN_enable;
	this.disable = BTN_disable;
	this.hide = BTN_hide;
	this.show = BTN_show;
	
	this.getImgSrc = BTN_getImgSrc;

}

function BTN_rollOn() {
	if (!this.curState) return;
	objBut = eval(this.strButObj);
	objBut.src = this.getImgSrc(2);
}
	
function BTN_rollOff() {
	if (!this.curState) return;
	objBut = eval(this.strButObj);
	objBut.src = this.getImgSrc(1);
}

function BTN_click() {
	if (!this.curState) return;
	eval(this.action);
}

function BTN_enable() {
	this.curState = true;
	objBut = eval(this.strButObj);
	objBut.src = this.getImgSrc(1);
	//objBut.style.cursor = "hand";
}

function BTN_disable() {
	this.curState = false;
	objBut = eval(this.strButObj);
	objBut.src = this.getImgSrc(0);
	//objBut.style.cursor = "arrow";
}

function BTN_hide() {
	this.curState = false;
	objBut = eval(this.strButObj);
	objBut.style.visibility = 'hidden';
}

function BTN_show() {
	this.curState = true;
	objBut = eval(this.strButObj);
	objBut.style.visibility = 'visible';
}

function BTN_getImgSrc(intState) {
	objBut = eval(this.strButObj);
	strSrc = objBut.src; 
	strSrcStem = strSrc.substring(0, strSrc.lastIndexOf("."));
	strSrcStem = strSrcStem.substring(0, strSrc.lastIndexOf("_"));
	
	return strSrcStem + "_" + intState + ".gif";
}


//--- Radio Button Object ---//

// strImageName - NAME property of IMG tag
// strAction - Code to eval on click
// flgInitState - initial button state (active/inactive)

// Button Naming Convention:
// _0 - Off/Disabled; _1 - Off/Normal; _2 - Off/Rollover;
// _3 - On/Disabled; _4 - On/Normal; _5 - On/Rollover;

function RadioButton(strImgName, strOnAction, strOffAction, flgInitButtonState, flgInitActiveState) {
	
	this.strButObj = "document." + strImgName;
	
	this.onAction = strOnAction;
	this.offAction = strOffAction;
	
	this.buttonState = flgInitButtonState;
	this.activeState = flgInitActiveState;
	this.deselect = true;
	
	this.objGroup = null;
	
	this.setGroup = RAD_setGroup;
	this.setDeselect = RAD_setDeselect;
	
	this.rollOn = RAD_rollOn;
	this.rollOff = RAD_rollOff;
	this.click = RAD_click;
	this.setButtonState = RAD_setButtonState;
	this.enable = RAD_enable;
	this.disable = RAD_disable;
	this.hide = RAD_hide;
	this.show = RAD_show;
	
	this.getImgSrc = RAD_getImgSrc;
}

function RAD_setGroup(objGroup) {
	this.objGroup = objGroup;
}

function RAD_setDeselect(flgDeselect) {
	this.deselect = flgDeselect;
}
	
function RAD_rollOn() {
	if (!this.activeState) return;
	objBut = eval(this.strButObj);
	if (this.buttonState) {
		objBut.src = this.getImgSrc(5);
	}
	else {
		objBut.src = this.getImgSrc(2);
	}
}

function RAD_rollOff() {
	if (!this.activeState) return;
	objBut = eval(this.strButObj);
	if (this.buttonState) {
		objBut.src = this.getImgSrc(4);
	}
	else {
		objBut.src = this.getImgSrc(1);
	}
}

function RAD_click() {
	if (!this.activeState) return;
	objBut = eval(this.strButObj);
	if (this.buttonState && this.deselect) {
		this.buttonState = false;
		objBut.src = this.getImgSrc(2);
		eval(this.offAction);
	}
	else if (!this.buttonState) {
		this.buttonState = true;
		objBut.src = this.getImgSrc(5);
		eval(this.onAction);
		
		if (this.objGroup) {
			this.objGroup.setGroupState(this);
		}
	}
}

function RAD_setButtonState(flgState) {
	this.buttonState = flgState;
	
	objBut = eval(this.strButObj);
	if (flgState) {
		objBut.src = this.getImgSrc(4);
	}
	else {
		objBut.src = this.getImgSrc(1);
	}
}
		
function RAD_enable() {
	this.activeState = true;
	objBut = eval(this.strButObj);
	
	if (this.buttonState) {
		objBut.src = this.getImgSrc(4);
	}
	else {
		objBut.src = this.getImgSrc(1);
	}
	//objBut.style.cursor = "hand";
}

function RAD_disable() {
	this.activeState = false;
	objBut = eval(this.strButObj);
	
	if (this.buttonState) {
		objBut.src = this.getImgSrc(3);
	}
	else {
		objBut.src = this.getImgSrc(0);
	}
	//objBut.style.cursor = "arrow";
}

function RAD_hide() {
	this.activeState = false;
	objBut = eval(this.strButObj);
	objBut.style.visibility = 'hidden';
}

function RAD_show() {
	this.activeState = true;
	objBut = eval(this.strButObj);
	objBut.style.visibility = 'visible';
}

function RAD_getImgSrc(intState) {
	objBut = eval(this.strButObj);
	strSrc = objBut.src; 
	strSrcStem = strSrc.substring(0, strSrc.lastIndexOf("."));
	strSrcStem = strSrcStem.substring(0, strSrc.lastIndexOf("_"));
	
	return strSrcStem + "_" + intState + ".gif";
}


//--- Radio Button Group ---//

// Control a group of radio button objects, defined by an array of object references (arrRadioButtons)
// flgDeselect indicated whether a selected option can be individually deselected by the user.

function RadioGroup(arrRadioButtons, flgDeselect) {

	this.arrRadioButtons = arrRadioButtons;
	
	this.setGroupState = RG_setGroupState;
	
	for (i=0; i<this.arrRadioButtons.length; i++) {
		this.arrRadioButtons[i].setGroup(this);
		this.arrRadioButtons[i].setDeselect(flgDeselect);
	}
}

function RG_setGroupState(objBut) {
	for (i=0; i<this.arrRadioButtons.length; i++) {
		if (this.arrRadioButtons[i] != objBut) {
			this.arrRadioButtons[i].setButtonState(false);
		}
	}
}


	

