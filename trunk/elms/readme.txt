ildasm.exe 	网上下载
ilasm1.exe		取自本机安装的.Net 1.x(C:\WINDOWS\Microsoft.NET\Framework\v1.1.4322)
ilasm2.exe		取自本机安装的.Net 2.x(C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727)

EStart-original.exe	安装后的原始文件
EStart.il	字串汉化在此
EStart.res	图标和版本信息

操作：
	ildasm  执行后有GUI界面，很容易使用
	ilasm1 EStart.il /output:EStart.exe 将 MSIL 文件 xxx.il 汇编生成EStart.exe 可执行文件

【1.字串修改】

首先查找“ldstr”，这个字串后面跟随双引号中的内容基本都是需要翻译的，例如本例：
ldstr "Currently Processing:"
ldstr "&File"
ldstr "&Open File..."
ldstr "Open &MD5 File..."
有些 .NET 程序会有“ldstr bytearray(XXX XXX XXX)”的语句代码，这括号 ( ) 中的 XXX 等字符有些是以 Unicode 编码显示。这就需要一些转换编码的工具自己手动来转换显示为正常字串，然后自行修改，再以 Unicode 编码填写回来，本例中没有出现，所以暂略，以后再行补充。
注意：ldstr 后若跟随这类字串，则不能汉化/翻译，因为这些是控件名称，这需要您准确的判断，如本例中的：
ldstr "webLink"
ldstr "currentlyProcessingLabel"

【2.字体修改】

那么翻译完之后，由于源程序的字体有时并不适合中文用户阅读，那么哪些是字体呢？幸好，这个 il 反编译代码文件中提供了字体名称和字号大小，也是跟随于 ldstr 之后，本例中找到如下两种字体：
ldstr "Courier New"
ldc.r4 8.25
ldstr "Microsoft Sans Serif"
ldc.r4 12.
对字体名称熟悉的朋友一下可以看出上述两个字串就是两种字体命名，而下面紧跟的“ldc.r4 8.25”和“ldc.r4 12.”就是字号大小尺寸。那么我们可以将上述的“Courier New”和“Microsoft Sans Serif”都改为“宋体”，紧跟的字号改为“9”，至于“12”是大字体可以保留不变。当然您也可以修改为在 2K/XP 上字体较美观的“Tahoma”8 号字。(Tahoma 8 号字与 宋体 9 号字在 2K/XP 上基本大小等同)
ldstr "宋体"
ldc.r4 9
ldstr "宋体"
ldc.r4 12.

帮助信息：
	在测试过程中，我发现 il 文件中的可翻译字串可以使用 Passolo 进行提取，这样可以大幅提高翻译效率。
	其他工具：Resource Hunter，有绿色版本
	il文件分析
		每个label均有三个位置出现：字体定义、label名称、label显示文本（直接文本或ASCII码），前一句是相同的ldfld ......
		每个button均有两次出现，button标志和显示内容
		