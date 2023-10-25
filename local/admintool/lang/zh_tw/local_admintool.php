<?php 
//require_once(__DIR__ . '/../en/local_admintool.php');
$string['pluginname']='管理工具，例如自定義用户上傳';
$string['uploaduser']="上傳SWD General Learner";
$string['duplicateuserinfile']='在上傳的文件中找到重複的用户';
$string['displaytotalrecords'] = '顯示 {$a->display}, 總計 {$a->total} 記錄';
$string['problemuserpreview']='問題用户預覽';
$string['ignoreusermessage']='如果點擊複選框，用户將被創建。';
$string['duplicateuserindb']='在數據庫中找到重複的用户';
$string['profile_field_chiname']='中文名稱';
$string['profile_field_engname']='英文名稱';
$string['profile_field_joindate']='加入日期';										  

//added by Felix
$string['activateaccount']='激活帳户';
$string['securityquestion']='安全問題';
$string['selectsecurityquestion']='選擇一個安全問題';
$string['missingselectquestion']='缺少安全問題';
$string['securityanswer']='安全答案';
$string['missingsecurityanswer']='缺少安全答案';
$string['wrongsecurityanswer']='錯誤的安全答案';
$string['activatedaccountmsg'] = '恭喜! 您的帳户被激活，您應該在 \'編輯個人資料\' 頁面輸入您的其他信息. 請點擊以下按鈕登錄頁面。';
$string['registeredusermsg']='您的註冊已被確認。 您的帳户將在3天內激活。';
$string['isapprovedNGO'] = '已批准 (NGO 用户)';
$string['isactivatedSWD'] = '已激活 (SWD 用户)';
$string['contactphoneheader'] = '辦公電話:';
$string['contactemailheader'] = '聯繫電子郵件:';
$string['contactphone'] = '2341 5546';
$string['contactemail'] = 'abc@example.com';
$string['forgotbothloginnamepwd'] = '聯繫我們';
$string['approve'] = '批准';
$string['confirmapprove'] = '確認/批准';
$string['emailapprovesubject'] = '{$a}: 帳户已批准';
$string['emailapprovecontent'] = '你好, {$a->firstname},

您的帳户已被批准。

要登錄系統 \'{$a->sitename}\', 請轉到此網址:
{$a->link}

在大多數郵件程序中，這應該顯示為一個藍色鏈接，您只需點擊即可。 
 如果不起作用，則將地址剪切並粘貼到web瀏覽器窗口頂部的地址行中。

如果您需要幫助，請聯繫網站管理員,
{$a->admin}';

$string['csvinvaliddateformat'] = '包含無效日期格式。 有效的格式是\'yyyy-mm-dd\'（例如2014-01-21）。';
$string['csvinvaliddateformatline'] = '<br>{$a->line}行: 無效的加入日期 \'{$a->joindate}\'';
$string['emailusercreatedsubject'] = '{$a}: 帳户激活';
$string['emailusercreatedcontent'] = '你好 {$a->firstname},

您的帳戶已創建。

您可以在資料設置中查看和編輯您的用戶資料設置。
請重置您的密碼的安全問題。

要登錄系統 \'{$a->sitename}\', 請到以下這個網址:

{$a->link}

在大多數郵件程序中，這應該顯示為一個藍色鏈接，您只需點擊即可。 
 如果不起作用，則將地址剪切並粘貼到web瀏覽器窗口頂部的地址行中。

如果您需要幫助，請聯繫網站管理員,
{$a->admin}';

$string['emailinactiveusercreatedcontent'] = '你好 {$a->firstname},

您的帳戶已創建

要激活您的新帳戶，請轉到此網址:
{$a->link}

在大多數郵件程序中，這應該顯示為一個藍色鏈接，您只需點擊即可。 
 如果不起作用，則將地址剪切並粘貼到web瀏覽器窗口頂部的地址行中。

如果您需要幫助，請聯繫網站管理員,
{$a->admin}';

$string['emailuserdeactivatesubject'] = '{$a}: 帳戶已停用';
$string['emailuserdeactivatecontent'] = '你好 {$a->firstname},

您的帳戶已被停用。

如果您需要幫助，請聯繫網站管理員,
{$a->admin}';

$string['servicesetting'] = '服務設置';
$string['servicesettingmss'] = '醫務社會服務';
$string['servicesettingos'] = '罪犯服務';
$string['servicesettingfcws'] = '家庭及兒童福利服務';
$string['servicesettinges'] = '長者服務';
$string['servicesettingrs'] = '復康服務';
$string['servicesettingcygws'] = '社區、青年及小組工作服務';
$string['servicesettingsss'] = '社會保障服務';
$string['servicesettingm'] = '管理';
$string['servicesettinga'] = '行政管理';
$string['servicesettingss'] = '支援服務';
$string['servicesettingo'] = '其他';
$string['servicesettingerror'] = '缺少服務設置';

$string['acinfo'] = '帳戶資料';
$string['loginidpolicy'] = '<span style="color:red; font-weight:bold">您的用戶名已預設，用戶名無法更改。 用戶名是用來登錄我們的系統，請把它記住。</span>';
$string['activekeyerror'] = '您的驗證資料錯誤，請聯繫管理員。';
$string['activateuseremailpolicy'] = '請使用您的個人電子郵件帳戶進行身份識別。 請勿使用以@結尾的電子郵件地址swd.gov.hk';

// navigation menu
$string['MY_PROFILE'] = '個人資料';
$string['MY_PRIVATE_FILES'] = '個人專用檔案';
$string['VISION'] = '理想、使命及價值觀宣言';
$string['FORUM'] = '討論區';
$string['WHATS_NEW'] = '最新消息';
$string['EXTERNAL_LINKS'] = '相關網址';
$string['CONTACT_US'] = '聯絡我們';

// archive course
$string['archive'] = '封存';
$string['archive_expired_course_task'] = '自動封存已過期的課程';
$string['confirmarchive'] = '您確認要封存課程嗎？ 請注意，該課程中的所有課件都將過期。';
$string['confirmunarchive'] = '您確認取消封存課程嗎？ 請注意，過期的課件將不會自動重新激活。';
$string['coursestatus_archive'] = '封存';

$string['register'] = '登記';
$string['login_enquiry_contact'] = '如有任何查詢，請致電 3974 5430 或 3107 8012與我們聯絡。';
$string['login_remark'] = '<b>備註：</b><br>如欲登入「interRAI學習平台」，請使用該學習平台的賬號登入。';