ildasm.exe 	��������
ilasm1.exe		ȡ�Ա�����װ��.Net 1.x(C:\WINDOWS\Microsoft.NET\Framework\v1.1.4322)
ilasm2.exe		ȡ�Ա�����װ��.Net 2.x(C:\WINDOWS\Microsoft.NET\Framework\v2.0.50727)

EStart-original.exe	��װ���ԭʼ�ļ�
EStart.il	�ִ������ڴ�
EStart.res	ͼ��Ͱ汾��Ϣ

������
	ildasm  ִ�к���GUI���棬������ʹ��
	ilasm1 EStart.il /output:EStart.exe �� MSIL �ļ� xxx.il �������EStart.exe ��ִ���ļ�

��1.�ִ��޸ġ�

���Ȳ��ҡ�ldstr��������ִ��������˫�����е����ݻ���������Ҫ����ģ����籾����
ldstr "Currently Processing:"
ldstr "&File"
ldstr "&Open File..."
ldstr "Open &MD5 File..."
��Щ .NET ������С�ldstr bytearray(XXX XXX XXX)���������룬������ ( ) �е� XXX ���ַ���Щ���� Unicode ������ʾ�������ҪһЩת������Ĺ����Լ��ֶ���ת����ʾΪ�����ִ���Ȼ�������޸ģ����� Unicode ������д������������û�г��֣��������ԣ��Ժ����в��䡣
ע�⣺ldstr �������������ִ������ܺ���/���룬��Ϊ��Щ�ǿؼ����ƣ�����Ҫ��׼ȷ���жϣ��籾���еģ�
ldstr "webLink"
ldstr "currentlyProcessingLabel"

��2.�����޸ġ�

��ô������֮������Դ�����������ʱ�����ʺ������û��Ķ�����ô��Щ�������أ��Һã���� il ����������ļ����ṩ���������ƺ��ֺŴ�С��Ҳ�Ǹ����� ldstr ֮�󣬱������ҵ������������壺
ldstr "Courier New"
ldc.r4 8.25
ldstr "Microsoft Sans Serif"
ldc.r4 12.
������������Ϥ������һ�¿��Կ������������ִ�����������������������������ġ�ldc.r4 8.25���͡�ldc.r4 12.�������ֺŴ�С�ߴ硣��ô���ǿ��Խ������ġ�Courier New���͡�Microsoft Sans Serif������Ϊ�����塱���������ֺŸ�Ϊ��9�������ڡ�12���Ǵ�������Ա������䡣��Ȼ��Ҳ�����޸�Ϊ�� 2K/XP ����������۵ġ�Tahoma��8 ���֡�(Tahoma 8 ������ ���� 9 ������ 2K/XP �ϻ�����С��ͬ)
ldstr "����"
ldc.r4 9
ldstr "����"
ldc.r4 12.

������Ϣ��
	�ڲ��Թ����У��ҷ��� il �ļ��еĿɷ����ִ�����ʹ�� Passolo ������ȡ���������Դ����߷���Ч�ʡ�
	�������ߣ�Resource Hunter������ɫ�汾
	il�ļ�����
		ÿ��label��������λ�ó��֣����嶨�塢label���ơ�label��ʾ�ı���ֱ���ı���ASCII�룩��ǰһ������ͬ��ldfld ......
		ÿ��button�������γ��֣�button��־����ʾ����
		