<?php 
//require_once(__DIR__ . '/../en/local_admintool.php');
$string['pluginname']='管理工具，例如自定义用户上传';
$string['uploaduser']="上传SWD General Learner";
$string['duplicateuserinfile']='在上传的文件中找到重复的用户';
$string['displaytotalrecords'] = '显示 {$a->display}, 总计 {$a->total} 记录';
$string['problemuserpreview']='问题用户预览';
$string['ignoreusermessage']='如果点击复选框，用户将被创建。';
$string['duplicateuserindb']='在数据库中找到重复的用户';
$string['profile_field_chiname']='中文名称';
$string['profile_field_engname']='英文名称';
$string['profile_field_joindate']='加入日期';										  

//added by Felix
$string['activateaccount']='激活帐户';
$string['securityquestion']='安全问题';
$string['selectsecurityquestion']='选择一个安全问题';
$string['missingselectquestion']='缺少安全问题';
$string['securityanswer']='安全答案';
$string['missingsecurityanswer']='缺少安全答案';
$string['wrongsecurityanswer']='错误的安全答案';
$string['activatedaccountmsg'] = '恭喜! 您的帐户被激活，您应该在 \'编辑个人资料\' 页面输入您的其他信息. 请点击以下按钮登录页面。';
$string['registeredusermsg']='您的注册已被确认。 您的帐户将在3天内激活。';
$string['isapprovedNGO'] = '已批准 (NGO 用户)';
$string['isactivatedSWD'] = '已激活 (SWD 用户)';
$string['contactphoneheader'] = '办公电话:';
$string['contactemailheader'] = '联系电子邮件:';
$string['contactphone'] = '2341 5546';
$string['contactemail'] = 'abc@example.com';
$string['forgotbothloginnamepwd'] = '联系我们';
$string['approve'] = '批准';
$string['confirmapprove'] = '确认/批准';
$string['emailapprovesubject'] = '{$a}: 帐户已批准';
$string['emailapprovecontent'] = '你好, {$a->firstname},

您的帐户已被批准。

要登录系统 \'{$a->sitename}\', 请转到此网址:
{$a->link}

在大多数邮件程序中，这应该显示为一个蓝色链接，您只需点击即可。 
 如果不起作用，则将地址剪切并粘贴到web浏览器窗口顶部的地址行中。

如果您需要帮助，请联系网站管理员,
{$a->admin}';

$string['csvinvaliddateformat'] = '包含无效日期格式。 有效的格式是\'yyyy-mm-dd\'（例如2014-01-21）。';
$string['csvinvaliddateformatline'] = '<br>{$a->line}行: 无效的加入日期 \'{$a->joindate}\'';
$string['emailusercreatedsubject'] = '{$a}: 帐户激活';
$string['emailusercreatedcontent'] = '你好 {$a->firstname},

您的帐户已创建。

您可以在资料设置中查看和编辑您的用户资料设置。
请重置您的密码的安全问题。

要登录系统 \'{$a->sitename}\', 请到以下这个网址:

{$a->link}

在大多数邮件程序中，这应该显示为一个蓝色链接，您只需点击即可。 
 如果不起作用，则将地址剪切并粘贴到web浏览器窗口顶部的地址行中。

如果您需要帮助，请联系网站管理员,
{$a->admin}';

$string['emailinactiveusercreatedcontent'] = '你好 {$a->firstname},

您的帐户已创建

要激活您的新帐户，请转到此网址:
{$a->link}

在大多数邮件程序中，这应该显示为一个蓝色链接，您只需点击即可。 
 如果不起作用，则将地址剪切并粘贴到web浏览器窗口顶部的地址行中。

如果您需要帮助，请联系网站管理员,
{$a->admin}';

$string['emailuserdeactivatesubject'] = '{$a}: 帐户已停用';
$string['emailuserdeactivatecontent'] = '你好 {$a->firstname},

您的帐户已被停用。

如果您需要帮助，请联系网站管理员,
{$a->admin}';

$string['servicesetting'] = '服务设置';
$string['servicesettingmss'] = '医务社会服务';
$string['servicesettingos'] = '罪犯服务';
$string['servicesettingfcws'] = '家庭及儿童福利服务';
$string['servicesettinges'] = '长者服务';
$string['servicesettingrs'] = '复康服务';
$string['servicesettingcygws'] = '社区、青年及小组工作服务';
$string['servicesettingsss'] = '社会保障服务';
$string['servicesettingm'] = '管理';
$string['servicesettinga'] = '行政管理';
$string['servicesettingss'] = '支援服务';
$string['servicesettingo'] = '其他';
$string['servicesettingerror'] = '缺少服务设置';

$string['acinfo'] = '帐户资料';
$string['loginidpolicy'] = '<span style="color:red; font-weight:bold">您的用户名已预设，用户名无法更改。 用户名是用来登录我们的系统，请把它记住。</span>';
$string['activekeyerror'] = '您的验证资料错误，请联系管理员。';
$string['activateuseremailpolicy'] = '请使用您的个人电子邮件帐户进行身份识别。 请勿使用以@结尾的电子邮件地址swd.gov.hk';

// navigation menu
$string['MY_PROFILE'] = '个人资料';
$string['MY_PRIVATE_FILES'] = '个人专用档案';
$string['VISION'] = '理想、使命及价值观宣言';
$string['FORUM'] = '讨论区';
$string['WHATS_NEW'] = '最新消息';
$string['EXTERNAL_LINKS'] = '相关网址';
$string['CONTACT_US'] = '联络我们';

// archive course
$string['archive'] = '封存';
$string['archive_expired_course_task'] = '自动封存已过期的课程';
$string['confirmarchive'] = '您确认要存档课程吗？ 请注意，该课程中的所有课件都将过期。';
$string['confirmunarchive'] = '您确认取消存档课程吗？ 请注意，过期的课件将不会自动重新激活。';
$string['coursestatus_archive'] = '封存';

$string['register'] = '登记';
$string['login_enquiry_contact'] = '如有任何查询，请致电 3974 5430 或 3107 8012与我们联络。';
$string['login_remark'] = '<b>备注：</b><br>如欲登入「interRAI学习平台」，请使用该学习平台的账号登入。';