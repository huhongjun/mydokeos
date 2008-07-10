function isLong(n,x,y) {
   return (n.length >= x) && (n.length <= y);
}
function isEmpty(t) {
   return (t == "");
}
function isNum(n) {
   return (n.search(/[^0-9]/) == -1);
}
function isId(t) {
   return (t.search(/[^a-z_0-9]/i) == -1);
}
function isText(t) {
   return (t.search(/[a-z0-9Ç-Ñ]/i) != -1);
}
function isEmail(t) {
   return (t.search(/.+@.+\..+/i) != -1)
}
function cleanNumber(c) {
  temp="";
  for (i=c.value.length-1; i>=0;i--) {
    ch = c.value.charAt(i);
    if (ch>='0' && ch<='9')
	  temp=ch + temp;
    else if (temp.length<=2 && (ch==',' || ch=='.'))
      temp="";
  }
  c.value = temp;  
  return true;
}
function cleanID(c) {
  c.value.replace(/[^0-9a-z_]/ig,"");
  return true;
}
function filterNumber(e) {
	if(e.keyCode != null) {
	    if(((e.keyCode >= 48) && (e.keyCode  < 58)) || (e.keyCode < 32) || (e.keyCode == 127)) //digits for MSIE + special symbols
	 		return e;
		else 
			return false;
	} else if (e.which != null) {
  	    if(((e.which >= 48) && (e.which  < 58)) || (e.which < 32) || (e.which == 127))    //digits for Netscape
	 		return e;
		else 
			return false;
	} else {
		return true;
	}
}
function filterID(e) {
	if(e.keyCode != null) { //MSIE
	    if( ((e.keyCode >= 48) && (e.keyCode < 58)) || //digits
			((e.keyCode >= 65) && (e.keyCode < 91)) || //capital letters
			((e.keyCode >= 97) && (e.keyCode < 123)) || //small letters
			 (e.keyCode == 95) || (e.keyCode < 32) || (e.keyCode == 127))// underscore + special symbols
	 		return e;
		else 
			return false;
	} else if (e.which != null) { //Netscape
	    if( ((e.which >= 48) && (e.which < 58)) ||  //digits 
			((e.which >= 65) && (e.which < 91)) || //capital letters
			((e.which >= 97) && (e.which < 123)) || //small letters
			 (e.which == 95) || (e.which < 32) || (e.which == 127))// underscore + special symbols
	 		return e;
		else 
			return false;
	} else {
		return true;
	}
}
function checkLogin(c) {
	if(isEmpty(c.value) || !isId(c.value)) {
       alert('Please enter a valid user id.')
       c.select();
       c.focus();
       return false;
	} 
	return true;
}
function checkPassword(c) {
	if(isEmpty(c.value) || !isId(c.value)) {
       alert('Please enter a valid password.')
       c.select();
       c.focus();
       return false;
	}
	return true;
}
function checkName(c) {
	if(isEmpty(c.value) || !isText(c.value)) {
       alert('Please enter a value for this field.')
       c.select();
       c.focus();
       return false;
	} 
	return true;
}
function checkFirstName(c) {
	if(isEmpty(c.value) || !isText(c.value)) {
       alert('Please enter a value for this field.')
       c.select();
       c.focus();
       return false;
	} 
	return true;
}
function checkMessage(c) {
	if(isEmpty(c.value) || !isText(c.value)) {
       alert('Please enter a value for this field.')
       c.select();
       c.focus();
       return false;
	} 
	return true;
}
function checkEmail(c) {
	if(isEmpty(c.value) || !isEmail(c.value)) {
       alert('Please enter a valid email address.')
       c.select();
       c.focus();
       return false;
	}
	return true;
}
function checkPasswordMatch(c1,c2) {
	if(c1.value != c2.value) {
       alert('The two passwords do not match.')
       c2.select();
       c2.focus();
       return false;
	}
	return true;
}
function checkRNum(c,message) {
    if (isEmpty(c.value) || !isNum(c.value)) {
	    alert(message);
    	c.select();
	    c.focus();
    	return false;
	}
	return true;
}
function checkNum(c,message) {
    if (!isEmpty(c.value) && !isNum(c.value)) {
	    alert(message);
    	c.select();
	    c.focus();
    	return false;
	}
	return true;
}
function checkRT(c,message,mn,mx) {
    if (isEmpty(c.value) || !isText(c.value) || !isLong(c.value,mn,mx)) {
	    alert(message);
    	c.select();
	    c.focus();
    	return false;
	}
	return true;
}
function checkRadio(c) {
    for (i = 0; i<c.length; i++) {
	   if (c[i].checked) return true;
	}
    alert("Please select an option.");
   	c[0].select();
    c[0].focus();
	return false;
}
function checkAccesClient(f) {
	if(!checkLogin(f.ClientID)) return false;
	if(!checkPassword(f.Password)) return false;
	return true;
}
function checkModifyAnnonces(f) {
	if(!checkLogin(f.username)) return false;
	if(!checkPassword(f.password)) return false;
	return true;
}
function checkModifyLoginAnnonces(f) {
	if(!checkLogin(f.username1)) return false;
	if(!checkPassword(f.password1)) return false;
	if(!checkPassword(f.password2)) return false;
    if(!checkPasswordMatch(f.password1,f.password2)) return false;
	return true;
}
function checkNewsletter(f) {
	if(!checkEmail(f.newsletter)) return false;
	return true;
}

function checkCombo(f)
{
   if (isNum( f.options[f.selectedIndex].value ) && !isEmpty(f.options[f.selectedIndex].value)) return true;
   alert("Please select an option.");
   f.focus();
   return false;
}
