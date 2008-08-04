课件模板
	基于HTML+JavaScript+Flash+XML

部署
	直接拷贝到apache的htdocs目录下即可，http://localhost/SCORM-CourseWare/index.htm
	当没有lms平台时离线方式运行，feedback和comment都不能用；
	只支持Internet Explorer；

配置
	EStart.exe.config

待办事项
	课件demo中文化，以便让课件相关人员了解其作用、功能和适用范围与价值；
		imsmanifest.xml：手工或用reload editor；
		模板固定内容：如home、exit、back、next等；
		
	课件模板用户手册，包括使用说明等；
	课件模板课件设计手册，包括布局、CSS、图片定制等；
	课件模板技术手册，包括xml文件定义、JavaScript开发等；

	修改模板以便重用
		第一步、在桌面软件中使用；
		第二步、迁移到dokeos中使用？

关于Flash模板
	delete层是模板开发人员留下的提示信息，正式发布时删除该层即可,而对应的HTML文件中已经写好了与Flash通信的JS函数
	Flash模板可参考Pachyderm的，够复杂够强大

2008/08/05 整理14个样例课件，找出3个汉化。