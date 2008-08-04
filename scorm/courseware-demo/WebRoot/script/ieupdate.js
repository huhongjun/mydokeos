flashfix = function() {
theObjects = document.getElementsByTagName("object");
for (var i = 0; i < theObjects.length; i++) {
theObjects[i].outerHTML = theObjects[i].outerHTML;
}
} 

if (window.attachEvent)
window.attachEvent("onload", flashfix)
else
window.onload=flashfix;