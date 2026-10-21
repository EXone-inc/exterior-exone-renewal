<?php
/** Fixed private lab only. Actual WP/plugin APIs; synthetic input; HTTP/mail intercepted. */
if ( PHP_SAPI !== 'cli' ) { exit(1); }
umask(0077);
require dirname(__DIR__) . '/import/guard.php';
$env = exterior_exone_import_environment();
$identity = exterior_exone_import_connect($env); $identity->close();
if (!is_file($env['wp_root'].'/wp-content/mu-plugins/exone-lab-isolation.php')) { exit(2); }
define('EXR_COEXISTENCE', true);
$_SERVER['HTTP_HOST']='127.0.0.1:18746'; $_SERVER['SERVER_NAME']='127.0.0.1'; $_SERVER['REQUEST_URI']='/';
require $env['wp_root'].'/wp-load.php';
if (DB_NAME!==$env['database'] || DB_HOST!=='localhost:'.$env['socket'] || get_option('home')!==$env['home'] || get_option('siteurl')!==$env['siteurl'] || get_option('stylesheet')!=='affinger-child' || CFTZ_VERSION!=='5.0.0' || !has_filter('pre_http_request') || !has_filter('pre_wp_mail')) { exit(3); }
require dirname(__DIR__,3).'/inc/coexistence.php';
require dirname(__DIR__,3).'/inc/coexistence-slack.php';
exterior_exone_coexistence_register_storage(); wp_set_current_user(1);
$checks=0; $captured=array(); $mailCalls=0; $httpCalls=0; $changed=array(); $report=array('operation'=>'slack-isolated-plugin-test','actual_http'=>0,'actual_mail'=>0);
function lab_slack_check($ok,$label) { global $checks; if(!$ok){throw new RuntimeException($label);} $checks++; }
function lab_slack_snapshot() {
	global $wpdb; $hashes=array();
	$ids=array('posts'=>'ID','postmeta'=>'meta_id','terms'=>'term_id','termmeta'=>'meta_id','term_taxonomy'=>'term_taxonomy_id','term_relationships'=>'object_id,term_taxonomy_id','options'=>'option_id','users'=>'ID','usermeta'=>'umeta_id','comments'=>'comment_ID','commentmeta'=>'meta_id');
	foreach($ids as $name=>$order){$hashes[$name]=hash('sha256',serialize($wpdb->get_results('SELECT * FROM `'.$wpdb->prefix.$name.'` ORDER BY '.$order,ARRAY_A)));}
	return $hashes;
}
$contact='https://hooks.slack.com/services/T000TEST/B000TEST/FAKECONTACTTOKEN';
$entry='https://hooks.slack.com/services/T000TEST/B000TEST/FAKEENTRYTOKEN';
add_filter('pre_http_request',function($pre,$args,$url)use(&$captured,&$httpCalls,$contact,$entry){
	$httpCalls++;
	if(!in_array($url,array($contact,$entry),true)){return new WP_Error('lab_unexpected_request','Blocked.');}
	$captured[]=array('url'=>$url,'args'=>$args);
	return new WP_Error('synthetic_slack_failure','Synthetic failure; never sent.');
},PHP_INT_MAX,3);
add_filter('pre_wp_mail',function()use(&$mailCalls){$mailCalls++;return true;},PHP_INT_MAX);
$before=null; $forms=null;
try {
	$forms=exterior_exone_slack_forms(); lab_slack_check(!is_wp_error($forms)&&count($forms)===3,'Three unique imported forms');
	foreach($forms as $id){lab_slack_check(get_post_meta($id,'_ctz_zapier',false)===array(),'Lab requires initially absent synthetic settings');}
	$before=lab_slack_snapshot();
	lab_slack_check(exterior_exone_slack_save(array('contact_url'=>$contact,'entry_url'=>$entry,'contact_active'=>'1','entry_active'=>'1'))===true,'Save through theme service');
	foreach($forms as $role=>$id){$changed[$id]=exterior_exone_slack_settings($role==='entry'?$entry:$contact,true);}
	$afterSave=lab_slack_snapshot(); foreach($before as $table=>$hash){if($table!=='postmeta'){lab_slack_check($afterSave[$table]===$hash,'Non-meta table unchanged');}}
	global $wpdb;
	$filtered=$wpdb->get_results('SELECT * FROM `'.$wpdb->prefix.'postmeta` WHERE NOT (meta_key=\'_ctz_zapier\' AND post_id IN ('.implode(',',array_map('intval',array_values($forms))).')) ORDER BY meta_id',ARRAY_A);
	lab_slack_check(hash('sha256',serialize($filtered))===$before['postmeta'],'Only three dedicated meta rows changed');
	ob_start(); exterior_exone_slack_admin_render(); $html=ob_get_clean();
	lab_slack_check(strpos($html,'FAKECONTACTTOKEN')===false&&strpos($html,'FAKEENTRYTOKEN')===false,'Rendered actual UI conceals stored URLs');
	$html=preg_replace('/(<input[^>]+name="_wpnonce"[^>]+value=")[^"]*(")/','$1visual-only$2',$html);
	$html=str_replace('<form method="post"','<form onsubmit="return false" method="post"',$html);
	$preview='<!doctype html><html lang="ja"><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>新テーマの Slack 通知</title><link rel="stylesheet" href="/retest/wp-includes/css/dashicons.min.css"><link rel="stylesheet" href="/retest/wp-admin/css/common.min.css"><link rel="stylesheet" href="/retest/wp-admin/css/forms.min.css"><style>body{background:#f0f0f1;margin:0;padding:12px}.wrap{margin:10px auto;max-width:1200px}input.large-text{box-sizing:border-box;max-width:100%}</style><body class="wp-admin wp-core-ui">'.$html.'</body></html>';
	file_put_contents($env['root'].'/site/slack-settings-preview.html',$preview);chmod($env['root'].'/site/slack-settings-preview.html',0600);
	$module=(new ReflectionClass('CFTZ_Module_CF7'))->newInstanceWithoutConstructor();
	foreach($forms as $role=>$id){
		$form=exterior_exone_coexistence_form_instance($id); lab_slack_check($form instanceof WPCF7_ContactForm,'Actual CF7 clone accepted');
		lab_slack_check($module->wpcf7_skip_mail(false,$form)===false,'Actual plugin preserves ordinary mail');
		$properties=$form->prop('ctz_zapier'); lab_slack_check($properties['send_mail']==='1'&&$properties['error_mails']===array(),'Actual clone receives scoped properties');
		$data=array('store-slug'=>'aomori','your-name'=>'架空の名前 "引用"','your-email'=>'fixture@example.invalid','your-message'=>"日本語\n<!channel> [your-email]",'your-name-last'=>'架空','your-name-first'=>'太郎','your-job'=>array('施工管理職'),'your-gender'=>array('無回答'),'your-contact'=>'採用の合成メッセージ','your-tel'=>'0000000000','your-postcode'=>'0000000','your-zip'=>'0000000','your-address'=>'合成住所','your-extended-address'=>'合成住所続き','your-company'=>'合成会社','not-allowed'=>'MUSTNOTAPPEAR');
		$_POST=wp_slash($data); $_FILES=array();
		$r=new ReflectionClass('WPCF7_Submission');$submission=$r->newInstanceWithoutConstructor();
		foreach(array('contact_form'=>$form,'status'=>'mail_sent','posted_data'=>$data) as $name=>$value){$p=$r->getProperty($name);$p->setAccessible(true);$p->setValue($submission,$value);}
		$p=$r->getProperty('instance');$p->setAccessible(true);$p->setValue(null,$submission);
		$count=count($captured);do_action('wpcf7_mail_sent',$form);
		lab_slack_check(count($captured)===$count+1,'Actual plugin sends one intercepted request');
		$request=end($captured); $decoded=json_decode($request['args']['body'],true);
		lab_slack_check($request['url']===($role==='entry'?$entry:$contact),'Actual target role mapping');
		lab_slack_check($request['args']['method']==='POST'&&$request['args']['redirection']===0&&$request['args']['sslverify']===true&&is_array($decoded)&&$decoded['parse']==='none'&&!$decoded['mrkdwn']&&!$decoded['link_names'],'Actual sender uses secure JSON args');
		lab_slack_check(strpos($decoded['text'],'MUSTNOTAPPEAR')===false&&strpos($decoded['text'],'<!channel>')===false,'Actual payload excludes unapproved values and live mentions');
		lab_slack_check($submission->get_status()==='mail_sent','Slack error does not change prior mail success');
	}
	$legacy=WPCF7_ContactForm::get_template();$legacySettings=exterior_exone_slack_settings($contact,true);$legacySettings['send_mail']='0';
	$legacy->set_properties(array('form'=>'[text your-name]','ctz_zapier'=>$legacySettings));
	lab_slack_check(!$legacy->id()&&$module->wpcf7_skip_mail(false,$legacy)===true,'Unsaved legacy template keeps plugin mail-skip behavior');
	$r=new ReflectionClass('WPCF7_Submission');$submission=$r->newInstanceWithoutConstructor();
	foreach(array('contact_form'=>$legacy,'status'=>'mail_sent','posted_data'=>array('your-name'=>'Legacy fixture')) as $name=>$value){$p=$r->getProperty($name);$p->setAccessible(true);$p->setValue($submission,$value);}
	$p=$r->getProperty('instance');$p->setAccessible(true);$p->setValue(null,$submission);
	$_POST=array('your-name'=>'Legacy fixture');$count=count($captured);do_action('wpcf7_mail_sent',$legacy);$request=end($captured);
	lab_slack_check(count($captured)===$count+1&&$request['args']['redirection']===10,'Actual legacy sender keeps default request arguments');
	lab_slack_check($submission->get_status()==='mail_failed','Actual legacy sender keeps existing error behavior');
	lab_slack_check($httpCalls===4&&$mailCalls===0,'All HTTP intercepted; no extra error-mail calls');
	$report['ok']=true;
}catch(Throwable $error){$report['ok']=false;$report['failure_type']=get_class($error);$report['failure_label']=$error->getMessage();}
finally {
	foreach($changed as $id=>$value){if(get_post_meta($id,'_ctz_zapier',false)===array($value)){delete_post_meta($id,'_ctz_zapier',$value);}}
	$report['restored_all_rows']=$before!==null&&lab_slack_snapshot()===$before;
	$report['old_theme_preserved']=get_option('stylesheet')==='affinger-child';
	$report['checks']=$checks;$report['intercepted_http']=$httpCalls;$report['intercepted_mail']=$mailCalls;$report['php']=PHP_VERSION;$report['plugin']=defined('CFTZ_VERSION')?CFTZ_VERSION:null;
	$report['ok']=!empty($report['ok'])&&$report['restored_all_rows']&&$report['old_theme_preserved'];
	$path=$env['root'].'/evidence/slack-integration-20261019-'.gmdate('His').'-'.bin2hex(random_bytes(3)).'.json';
	file_put_contents($path,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n");chmod($path,0600);
	echo json_encode($report,JSON_UNESCAPED_SLASHES)."\n";
}
exit($report['ok']?0:1);
