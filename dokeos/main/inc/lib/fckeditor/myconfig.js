FCKConfig.IMUploadPath = '';
FCKConfig.FlashUploadPath = '' ;
FCKConfig.AudioUploadPath = '' ;
FCKConfig.UserStatus = 'teacher' ;

FCKConfig.ToolbarSets["Question"] = [
	['Source','DocProps','-','NewPage','Preview','-'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	['Link','Unlink','Anchor'],
	['ImageManager','Flash','Video','MP3','Table','Rule','Smiley','SpecialChar','UniversalKey'],
	['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField'],
	'/',
	['Style','FontFormat','FontName','FontSize'],['Attachment']
] ;

FCKConfig.ToolbarSets["Middle"] = [
	['FontSize','Bold','Italic','Underline','StrikeThrough','TextColor','-','OrderedList','UnorderedList','-','Rule','Link','Table','-','ImageManager','Flash']
] ;

FCKConfig.ToolbarSets["Small"] = [
	['Bold','Italic','Underline','StrikeThrough','Link','ImageManager','Flash','OrderedList','UnorderedList','Table']
] ;

FCKConfig.ToolbarSets["Profil"] = [
	['Bold','Italic','Underline','StrikeThrough','Link','OrderedList','UnorderedList']
] ;

FCKConfig.ToolbarSets["Blog"] = [
	['Bold','Italic','Underline','StrikeThrough','Link','ImageManager','OrderedList','UnorderedList','Table']
] ;

FCKConfig.ToolbarSets["Announcements"] = [
	['Bold','Italic','Underline','StrikeThrough','Link','ImageManager','OrderedList','UnorderedList','Table']
] ;

FCKConfig.ToolbarSets["Full"] = [
	['PasteWord','Link','Anchor','-','ImageManager','Flash','MP3','Video','Table','Rule','-','Subscript', 'Superscript','-','OrderedList','UnorderedList','Outdent','Indent','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],'/',['FontName','FontSize','Bold','Italic','Underline','StrikeThrough','TextColor', 'BGColor','-','Source','mimetex']
] ;

FCKConfig.ToolbarSets["Comment"] = [
	['Bold','Italic','Underline','StrikeThrough']
] ;

FCKConfig.ToolbarSets["ForumLight"] = [
	['Bold','Italic','Underline','StrikeThrough']
] ;

FCKConfig.ToolbarSets["NewTest"] = [
	['Bold','Italic','Underline','StrikeThrough','Link','ImageManager','Flash','MP3','OrderedList','UnorderedList','Table']
] ;

FCKConfig.ToolbarSets["TestComment"] = [
		['FontName','FontSize','TextColor','BGColor'],['Bold','Italic','Underline','StrikeThrough','Subscript', 'Superscript','Link','ImageManager','Flash','MP3','Video','OrderedList','UnorderedList','Table']
] ;

FCKConfig.ToolbarSets["Test"] = [
	['Bold','Italic','Underline','StrikeThrough','Subscript','Superscript','Link','ImageManager','MP3','OrderedList','UnorderedList','Table']
] ;

FCKConfig.ToolbarSets["Survey"] = [
	['FontSize','Bold','Italic','TextColor','-','OrderedList','UnorderedList','-','Rule','Link','Table','-','ImageManager','Source']
] ;

var sOtherPluginPath = FCKConfig.BasePath.substr(0, FCKConfig.BasePath.length - 7) + 'editor/plugins/' ;
FCKConfig.Plugins.Add("MP3", "en", sOtherPluginPath ) ;
FCKConfig.Plugins.Add("Video", "en", sOtherPluginPath ) ;
FCKConfig.Plugins.Add("Attachment", "en", sOtherPluginPath ) ;
FCKConfig.Plugins.Add("mimetex", "en", sOtherPluginPath ) ; 
