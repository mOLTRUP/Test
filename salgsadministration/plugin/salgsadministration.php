<?php
/**
 * Plugin Name: Salgsadministration
 * Description: Selvstændig lead- og pipelinestyring med rollebaseret adgang, opfølgning, rapporter og revisionslog.
 * Version: 1.0.0-beta.1
 * Requires at least: 6.3
 * Requires PHP: 8.0
 * Text Domain: salgsadministration
 */
if (!defined('ABSPATH')) { exit; }

final class SA_Sales {
    const NS = 'salgsadmin/v1';
    const ROLES = ['seller'=>'sa_seller', 'manager'=>'sa_manager', 'leadership'=>'sa_leadership', 'admin'=>'sa_administrator'];
    const STATES = ['new','contacted','dialog','offer','negotiation','won','lost'];
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'routes']);
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_shortcode('salgsadministration', [__CLASS__, 'shortcode']);
        add_action('wp_login', [__CLASS__, 'login_ok'], 10, 2);
        add_action('wp_login_failed', [__CLASS__, 'login_failed']);
        add_filter('wp_authenticate_user', [__CLASS__, 'check_login'], 10, 2);
        add_filter('rest_pre_serve_request', [__CLASS__, 'serve_csv'], 10, 4);
    }
    private static function table($part) { global $wpdb; return $wpdb->prefix . 'sa_' . $part; }
    public static function activate() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $collate = $wpdb->get_charset_collate();
        $leads = self::table('leads'); $audit = self::table('audit');
        dbDelta("CREATE TABLE $leads (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            company varchar(255) NOT NULL,
            vat varchar(64) NOT NULL DEFAULT '',
            country varchar(12) NOT NULL,
            office varchar(140) NOT NULL,
            contact varchar(200) NOT NULL,
            phone varchar(80) NOT NULL,
            email varchar(190) NOT NULL DEFAULT '',
            owner_id bigint(20) unsigned NOT NULL,
            amount_eur decimal(16,2) NOT NULL DEFAULT 0,
            vehicles int unsigned NOT NULL DEFAULT 0,
            probability tinyint unsigned NOT NULL DEFAULT 0,
            products longtext NOT NULL,
            status varchar(30) NOT NULL DEFAULT 'new',
            next_followup datetime DEFAULT NULL,
            reminder_days smallint unsigned NOT NULL DEFAULT 0,
            followup_reason text,
            notes longtext,
            attachment_url text,
            outcome_reason varchar(140) NOT NULL DEFAULT '',
            outcome_note text,
            close_date date DEFAULT NULL,
            created_by bigint(20) unsigned NOT NULL,
            updated_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY owner_id (owner_id),
            KEY area (country,office),
            KEY status (status),
            KEY next_followup (next_followup),
            KEY vat (vat)
        ) $collate;");
        dbDelta("CREATE TABLE $audit (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            lead_id bigint(20) unsigned NOT NULL DEFAULT 0,
            actor_id bigint(20) unsigned NOT NULL DEFAULT 0,
            event varchar(90) NOT NULL,
            changes longtext,
            changed_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY lead_id (lead_id),
            KEY actor_id (actor_id),
            KEY changed_at (changed_at)
        ) $collate;");
        foreach (self::ROLES as $key=>$slug) {
            if (!get_role($slug)) add_role($slug, 'Salgsadmin – ' . $key, ['read'=>true, 'sa_access'=>true]);
            else get_role($slug)->add_cap('sa_access');
        }
        $admin=get_role('administrator'); if ($admin) $admin->add_cap('sa_access');
        if (get_option('sa_lists') === false) add_option('sa_lists', [
            'countries'=>[['DK','Danmark'],['DE','Tyskland'],['PL','Polen'],['FR','Frankrig'],['IT','Italien'],['RO','Rumænien'],['BG','Bulgarien'],['SK','Slovakiet'],['LT','Litauen'],['GB','Storbritannien'],['NL','Nederlandene']],
            'offices'=>['DK'=>['Padborg','København'],'DE'=>['Hamborg','Berlin'],'PL'=>['Warszawa','Poznań'],'FR'=>['Paris','Lyon'],'IT'=>['Milano','Rom'],'RO'=>['Bukarest'],'BG'=>['Sofia'],'SK'=>['Bratislava'],'LT'=>['Vilnius'],'GB'=>['London'],'NL'=>['Rotterdam']],
            'products'=>['Moms','Vejskat','Juridisk hjælp','Færge']
        ], '', false);
    }
    private static function role($u=null) {
        if (!$u) $u=wp_get_current_user();
        if (!$u || !$u->exists()) return 'guest';
        if (in_array('administrator', (array)$u->roles, true) || in_array('sa_administrator', (array)$u->roles, true)) return 'admin';
        foreach (['manager','leadership','seller'] as $name) if (in_array(self::ROLES[$name], (array)$u->roles, true)) return $name;
        return 'guest';
    }
    private static function disabled($id) { return (bool)get_user_meta($id,'sa_disabled',true); }
    private static function areas($id) {
        $countries=get_user_meta($id,'sa_countries',true);$offices=get_user_meta($id,'sa_offices',true);
        return ['countries'=>is_array($countries)?$countries:[], 'offices'=>is_array($offices)?$offices:[]];
    }
    private static function identity($u, $private=false) {
        $area=self::areas($u->ID);
        return ['id'=>(int)$u->ID, 'name'=>$u->display_name, 'email'=>$private?$u->user_email:'',
            'role'=>self::role($u), 'countries'=>$area['countries'], 'offices'=>$area['offices'],
            'lang'=>get_user_meta($u->ID,'sa_lang',true) ?: 'da',
            'disabled'=>$private?self::disabled($u->ID):false];
    }
    public static function menu() {
        add_menu_page('Salgsadministration','Salgsadministration','sa_access','salgsadministration',[__CLASS__,'admin_screen'],'dashicons-chart-line',29);
    }
    public static function admin_screen() { echo '<div class="wrap">'.self::shortcode().'</div>'; }
    public static function shortcode() {
        if (!is_user_logged_in()) return '<p>Log ind for at bruge Salgsadministration. <a href="'.esc_url(wp_login_url(get_permalink())).'">Log ind</a></p>';
        if (!current_user_can('sa_access') || self::disabled(get_current_user_id())) return '<p>Du har ikke adgang til Salgsadministration.</p>';
        wp_enqueue_style('sa-interface', plugins_url('assets/app.css',__FILE__), [], '1.0.0');
        wp_enqueue_script('sa-interface', plugins_url('assets/app.js',__FILE__), [], '1.0.0', true);
        wp_add_inline_script('sa-interface','window.SA_CONFIG='.wp_json_encode([
            'mode'=>'wp', 'rest'=>esc_url_raw(rest_url(self::NS.'/')),
            'nonce'=>wp_create_nonce('wp_rest'), 'loginUrl'=>wp_login_url()
        ]).';','before');
        return '<div id="sa-app"></div>';
    }
    public static function check_login($user,$password) {
        if ($user instanceof WP_User && self::disabled($user->ID)) { self::log(0,$user->ID,'auth_denied',['reason'=>'disabled']);return new WP_Error('sa_disabled','Denne konto er deaktiveret.'); }
        return $user;
    }
    public static function login_ok($login,$user) { self::log(0,$user->ID,'login_success'); }
    public static function login_failed($login) { self::log(0,0,'login_failed',['login_hash'=>wp_hash((string)$login)]); }
    private static function log($id,$actor,$event,$changes=[]) {
        global $wpdb;
        $wpdb->insert(self::table('audit'),['lead_id'=>(int)$id,'actor_id'=>(int)$actor,'event'=>$event,'changes'=>wp_json_encode($changes,JSON_UNESCAPED_UNICODE),'changed_at'=>current_time('mysql')],['%d','%d','%s','%s','%s']);
    }
    public static function permission($request) {
        $user=wp_get_current_user();
        $ok=$user->exists() && current_user_can('sa_access') && !self::disabled($user->ID);
        if (!$ok) self::log(0,$user->ID,'api_denied',['route'=>sanitize_text_field($request->get_route())]);
        return $ok ? true : new WP_Error('sa_no_access','Ikke adgang.', ['status'=>403]);
    }
    public static function admin_permission($request) {
        $p=self::permission($request); if (is_wp_error($p)) return $p;
        if (self::role()==='admin') return true;
        self::log(0,get_current_user_id(),'api_denied',['route'=>$request->get_route(),'reason'=>'admin_only']);
        return new WP_Error('sa_forbidden','Kun administrator.', ['status'=>403]);
    }
    public static function routes() {
        $r=function($path,$methods,$callback,$admin=false){register_rest_route(self::NS,$path,['methods'=>$methods,'callback'=>[__CLASS__,$callback],'permission_callback'=>[__CLASS__,$admin?'admin_permission':'permission']]);};
        $r('/bootstrap','GET','bootstrap');
        $r('/leads','GET','get_leads');
        $r('/leads','POST','create_lead');
        $r('/leads/(?P<id>\d+)','GET','single_lead');
        $r('/leads/(?P<id>\d+)','PUT','update_lead');
        $r('/audit/(?P<id>\d+)','GET','audit');
        $r('/lists','PUT','set_lists',true);
        $r('/users','POST','create_user',true);
        $r('/users/(?P<id>\d+)','PUT','update_user',true);
        $r('/preferences','PUT','preferences');
        $r('/export','GET','export_csv');
    }
    private static function users() {
        $admin=self::role()==='admin'; $all=[];
        foreach(get_users(['role__in'=>array_values(self::ROLES),'number'=>1000,'orderby'=>'display_name']) as $u) $all[]=self::identity($u,$admin || get_current_user_id()===$u->ID);
        // Core WordPress administrators are authorized but may not have custom SA roles.
        foreach(get_users(['role'=>'administrator','number'=>100]) as $u) if (!in_array($u->ID,array_column($all,'id'),true)) $all[]=self::identity($u,$admin);
        return $all;
    }
    private static function scoped_where($user=null) {
        global $wpdb;
        $user=$user?:wp_get_current_user();$role=self::role($user);
        if ($role==='admin'||$role==='leadership') return ['1=1',[]];
        if ($role==='seller') return ['owner_id=%d',[(int)$user->ID]];
        if ($role==='manager') {
            $a=self::areas($user->ID);$where=[];$params=[];
            if ($a['countries']) {$where[]='country IN ('.implode(',',array_fill(0,count($a['countries']),'%s')).')';$params=array_merge($params,$a['countries']);}
            if ($a['offices']) {$where[]="CONCAT(country,'|',office) IN (".implode(',',array_fill(0,count($a['offices']),'%s')).')';$params=array_merge($params,$a['offices']);}
            return $where?['('.implode(' OR ',$where).')',$params]:['1=0',[]];
        }
        return ['1=0',[]];
    }
    private static function fetch_leads($limit=2000) {
        global $wpdb;
        [$where,$params]=self::scoped_where();$table=self::table('leads');
        $sql="SELECT * FROM $table WHERE $where ORDER BY updated_at DESC, id DESC LIMIT ".max(1,min(10000,(int)$limit));
        if ($params) $sql=$wpdb->prepare($sql,...$params);
        return array_map([__CLASS__,'decode_lead'], $wpdb->get_results($sql,ARRAY_A) ?: []);
    }
    public static function decode_lead($l) {
        $l['id']=(int)$l['id'];$l['owner_id']=(int)$l['owner_id'];
        $l['created_by']=(int)$l['created_by'];$l['updated_by']=(int)$l['updated_by'];
        $l['amount_eur']=(float)$l['amount_eur'];$l['vehicles']=(int)$l['vehicles'];
        $l['probability']=(int)$l['probability'];$l['reminder_days']=(int)$l['reminder_days'];
        $l['products']=json_decode($l['products'],true) ?: [];
        $l['next_followup']=$l['next_followup']?str_replace(' ','T',substr($l['next_followup'],0,16)):'';
        $l['close_date']=$l['close_date'] ?: '';
        return $l;
    }
    private static function can_read($lead) {
        if (!$lead) return false;$role=self::role();
        if ($role==='admin'||$role==='leadership') return true;
        if ($role==='seller') return (int)$lead['owner_id']===get_current_user_id();
        $a=self::areas(get_current_user_id());
        return in_array($lead['country'],$a['countries'],true)||in_array($lead['country'].'|'.$lead['office'],$a['offices'],true);
    }
    private static function can_write($lead) { return self::role()!=='leadership' && self::can_read($lead); }
    private static function raw_lead($id) {
        global $wpdb; return $wpdb->get_row($wpdb->prepare('SELECT * FROM '.self::table('leads').' WHERE id=%d',(int)$id),ARRAY_A);
    }
    private static function forbidden() { self::log(0,get_current_user_id(),'api_denied',['reason'=>'object_scope']);return new WP_Error('sa_forbidden','Du har ikke adgang til dette lead.', ['status'=>403]); }
    public static function single_lead($request) {
        $l=self::raw_lead($request['id']);if (!$l) return new WP_Error('sa_missing','Lead findes ikke.',['status'=>404]);
        if (!self::can_read($l)) return self::forbidden();return self::decode_lead($l);
    }
    public static function get_leads() {return self::fetch_leads();}
    private static function summary() {
        global $wpdb;$t=self::table('leads');$y=(int)current_time('Y');
        $rows=$wpdb->get_results($wpdb->prepare("SELECT country,
            SUM(CASE WHEN status NOT IN ('won','lost') THEN 1 ELSE 0 END) active,
            SUM(CASE WHEN status='won' AND YEAR(close_date)=%d THEN 1 ELSE 0 END) won,
            SUM(CASE WHEN status='lost' AND YEAR(close_date)=%d THEN 1 ELSE 0 END) lost,
            SUM(CASE WHEN status NOT IN ('won','lost') THEN amount_eur ELSE 0 END) value,
            SUM(CASE WHEN status NOT IN ('won','lost') THEN amount_eur*probability/100 ELSE 0 END) weighted
            FROM $t GROUP BY country",$y,$y),ARRAY_A) ?: [];
        $options=get_option('sa_lists',[]);$names=[];foreach(($options['countries']??[]) as $p) $names[$p[0]]=$p[1];
        return array_map(function($x)use($names){return ['id'=>$x['country'],'name'=>$names[$x['country']]??$x['country'],'active'=>(int)$x['active'],'won'=>(int)$x['won'],'lost'=>(int)$x['lost'],'value'=>(float)$x['value'],'weighted'=>(float)$x['weighted']];},$rows);
    }
    private static function seller_summary() {
        global $wpdb;$t=self::table('leads');
        $rows=$wpdb->get_results("SELECT country,owner_id,
            SUM(CASE WHEN status NOT IN ('won','lost') THEN 1 ELSE 0 END) active,
            SUM(CASE WHEN status='won' THEN 1 ELSE 0 END) won,
            SUM(CASE WHEN status='lost' THEN 1 ELSE 0 END) lost,
            SUM(CASE WHEN created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY) THEN 1 ELSE 0 END) newWeek
            FROM $t GROUP BY country,owner_id",ARRAY_A) ?: [];
        return array_map(function($x){$u=get_user_by('id',(int)$x['owner_id']);return ['country'=>$x['country'],'name'=>$u?$u->display_name:'Ukendt','active'=>(int)$x['active'],'won'=>(int)$x['won'],'lost'=>(int)$x['lost'],'newWeek'=>(int)$x['newWeek']];},$rows);
    }
    public static function bootstrap() {
        return ['actor'=>self::identity(wp_get_current_user(),true), 'users'=>self::users(),
            'lists'=>get_option('sa_lists',[]),'leads'=>self::fetch_leads(),
            'countrySummary'=>self::summary(),'sellerSummary'=>self::seller_summary()];
    }
    private static function is_active($s) {return !in_array($s,['won','lost'],true);}
    private static function valid_date($s,$format) {if (!$s) return false;$dt=DateTimeImmutable::createFromFormat('!'.$format,$s,wp_timezone());return $dt && $dt->format($format)===$s;}
    private static function validate($r,$before=null) {
        $lists=get_option('sa_lists',[]);$input=$r->get_json_params();if (!is_array($input)) $input=[];
        $v=[];$texts=['company','vat','country','office','contact','phone','email','status','followup_reason','notes','outcome_reason','outcome_note','transfer_reason'];
        foreach($texts as $field) $v[$field]=sanitize_textarea_field((string)($input[$field]??''));
        $v['attachment_url']=esc_url_raw((string)($input['attachment_url']??''));
        $v['owner_id']=absint($input['owner_id']??get_current_user_id());
        $v['amount_eur']=is_numeric($input['amount_eur']??null)?(float)$input['amount_eur']:-1;
        $v['vehicles']=filter_var($input['vehicles']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]]);
        $v['probability']=filter_var($input['probability']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>100]]);
        $v['reminder_days']=filter_var($input['reminder_days']??0,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>365]]);
        $v['products']=array_values(array_unique(array_map('sanitize_text_field',is_array($input['products']??null)?$input['products']:[])));
        $v['next_followup']=sanitize_text_field((string)($input['next_followup']??''));
        $v['close_date']=sanitize_text_field((string)($input['close_date']??''));
        $is_closed=!self::is_active($v['status']);
        if (!$v['company']||!$v['country']||!$v['office']||!$v['contact']||!$v['phone']||!$v['owner_id']||$v['amount_eur']<0||$v['vehicles']===false||$v['probability']===false||$v['reminder_days']===false||!$v['products']||!in_array($v['status'],self::STATES,true)) return new WP_Error('sa_validation','Udfyld og kontrollér alle obligatoriske felter.',['status'=>422]);
        if ($v['email']&&!is_email($v['email'])) return new WP_Error('sa_email','Ugyldig e-mailadresse.',['status'=>422]);
        $validCountry=false;foreach(($lists['countries']??[]) as $a) if ($a[0]===$v['country']) $validCountry=true;
        if (!$validCountry||!in_array($v['office'],$lists['offices'][$v['country']]??[],true)) return new WP_Error('sa_area','Vælg et gyldigt land og kontor.',['status'=>422]);
        if (array_diff($v['products'],$lists['products']??[])) return new WP_Error('sa_products','Et eller flere produkter er ugyldige.',['status'=>422]);
        $owner=get_user_by('id',$v['owner_id']);if (!$owner||!in_array(self::role($owner),['seller','manager'],true)||self::disabled($owner->ID)) return new WP_Error('sa_owner','Ansvarlig sælger er ikke aktiv.',['status'=>422]);
        if (self::role()==='seller'&&($v['owner_id']!==get_current_user_id()||$before&&(int)$before['owner_id']!==get_current_user_id()))return self::forbidden();
        if (self::role()==='manager') {
            $test=['country'=>$v['country'],'office'=>$v['office'],'owner_id'=>$v['owner_id']];
            if (!self::can_read($test)) return self::forbidden();
        }
        if (self::role()==='leadership') return self::forbidden();
        if ($before && (int)$before['owner_id']!==$v['owner_id'] && !$v['transfer_reason']) return new WP_Error('sa_transfer','Overførsel kræver en begrundelse.',['status'=>422]);
        if (!$is_closed) {
            if (!self::valid_date($v['next_followup'],'Y-m-d\TH:i')) return new WP_Error('sa_followup','Aktivt lead kræver en gyldig opfølgningsdato og tid.',['status'=>422]);
            if (substr($v['next_followup'],0,10)<current_time('Y-m-d')&&!$v['followup_reason'])return new WP_Error('sa_followup_past','Tidligere opfølgning kræver en begrundelse.',['status'=>422]);
            $v['close_date']=null;$v['outcome_reason']='';$v['outcome_note']='';
        } else {
            if (!self::valid_date($v['close_date'],'Y-m-d')||!$v['outcome_reason'])return new WP_Error('sa_outcome','Afslutningsdato og udfaldsårsag er obligatoriske.',['status'=>422]);
            if ($v['outcome_reason']==='Andet'&&!$v['outcome_note'])return new WP_Error('sa_outcome_note','Andet kræver en forklarende note.',['status'=>422]);
            $v['next_followup']=null;
        }
        if ($v['attachment_url']&&!str_starts_with($v['attachment_url'],'https://'))return new WP_Error('sa_https','Dokumentlink skal bruge HTTPS.',['status'=>422]);
        if ($v['vat']) {
            global $wpdb;$t=self::table('leads');
            $duplicate=$wpdb->get_var($wpdb->prepare("SELECT id FROM $t WHERE vat=%s AND status NOT IN ('won','lost') AND id<>%d LIMIT 1",$v['vat'],$before?(int)$before['id']:0));
            if ($duplicate) return new WP_Error('sa_duplicate','Et aktivt lead med dette VAT-nummer findes allerede.',['status'=>409]);
        }
        unset($v['transfer_reason']);$v['email']=sanitize_email($v['email']);$v['products']=wp_json_encode($v['products'],JSON_UNESCAPED_UNICODE);
        if ($v['next_followup']) $v['next_followup']=str_replace('T',' ',$v['next_followup']).':00';
        return $v;
    }
    public static function create_lead($r) {
        if (self::role()==='leadership') return self::forbidden();
        $v=self::validate($r);if (is_wp_error($v))return $v;
        global $wpdb;$v['created_by']=get_current_user_id();$v['updated_by']=$v['created_by'];$v['created_at']=current_time('mysql');$v['updated_at']=$v['created_at'];
        $ok=$wpdb->insert(self::table('leads'),$v);
        if (!$ok)return new WP_Error('sa_db','Kunne ikke gemme lead.',['status'=>500]);
        $id=(int)$wpdb->insert_id;self::log($id,get_current_user_id(),'lead_created',['company'=>$v['company'],'owner_id'=>$v['owner_id']]);
        return self::decode_lead(self::raw_lead($id));
    }
    public static function update_lead($r) {
        $old=self::raw_lead($r['id']);if (!$old)return new WP_Error('sa_missing','Lead findes ikke.',['status'=>404]);
        if (!self::can_write($old))return self::forbidden();
        $v=self::validate($r,$old);if (is_wp_error($v))return $v;
        global $wpdb;$v['updated_by']=get_current_user_id();$v['updated_at']=current_time('mysql');
        $ok=$wpdb->update(self::table('leads'),$v,['id'=>(int)$r['id']]);
        if ($ok===false)return new WP_Error('sa_db','Kunne ikke opdatere lead.',['status'=>500]);
        $changes=[];foreach(['owner_id','amount_eur','probability','status','outcome_reason','outcome_note','close_date'] as $field)if((string)($old[$field]??'')!==(string)($v[$field]??''))$changes[$field]=['old'=>$old[$field]??null,'new'=>$v[$field]??null];
        if ($changes) { $p=$r->get_json_params();if(isset($changes['owner_id']))$changes['transfer_reason']=sanitize_text_field((string)($p['transfer_reason']??''));self::log((int)$r['id'],get_current_user_id(),'lead_updated',$changes); }
        return self::decode_lead(self::raw_lead($r['id']));
    }
    public static function audit($r) {
        global $wpdb;$l=self::raw_lead($r['id']);if(!$l)return new WP_Error('sa_missing','Lead findes ikke.',['status'=>404]);
        if(!self::can_read($l))return self::forbidden();
        $rows=$wpdb->get_results($wpdb->prepare('SELECT actor_id,event,changes,changed_at FROM '.self::table('audit').' WHERE lead_id=%d ORDER BY id DESC LIMIT 200',(int)$r['id']),ARRAY_A);
        return array_map(function($a){$u=get_user_by('id',(int)$a['actor_id']);$a['actor_name']=$u?$u->display_name:'Ukendt';return $a;},$rows?:[]);
    }
    public static function preferences($r) {
        $lang=sanitize_key($r->get_param('lang'));
        if(!in_array($lang,['da','pl','en','fr','de','it','ro','bg','sk','lt'],true))return new WP_Error('sa_language','Ugyldigt sprog.',['status'=>422]);
        update_user_meta(get_current_user_id(),'sa_lang',$lang);return ['lang'=>$lang];
    }
    public static function set_lists($r) {
        $p=$r->get_json_params();if(!is_array($p))return new WP_Error('sa_lists','Ugyldige stamdata.',['status'=>422]);
        $countries=[];foreach(($p['countries']??[])as $row){if(!is_array($row)||count($row)!==2)continue;$c=strtoupper(sanitize_key($row[0]));$name=sanitize_text_field($row[1]);if($c&&$name)$countries[]=[$c,$name];}
        $offices=[];foreach(($p['offices']??[]) as $c=>$a){$c=strtoupper(sanitize_key($c));$offices[$c]=array_values(array_unique(array_filter(array_map('sanitize_text_field',(array)$a))));}
        $products=array_values(array_unique(array_filter(array_map('sanitize_text_field',(array)($p['products']??[])))));
        if (!$countries||!$products)return new WP_Error('sa_lists','Angiv mindst et land og et produkt.',['status'=>422]);
        update_option('sa_lists',['countries'=>$countries,'offices'=>$offices,'products'=>$products],false);
        self::log(0,get_current_user_id(),'lists_updated',['countries'=>count($countries),'products'=>count($products)]);
        return ['ok'=>true];
    }
    private static function user_values($r,$old=null) {
        $a=$r->get_json_params();$name=sanitize_text_field($a['name']??'');$email=sanitize_email($a['email']??'');$role=sanitize_key($a['role']??'');
        if (!$name||!is_email($email)||!isset(self::ROLES[$role]))return new WP_Error('sa_user','Udfyld gyldigt navn, e-mail og rolle.',['status'=>422]);
        $lang=sanitize_key($a['lang']??'da');if(!in_array($lang,['da','pl','en','fr','de','it','ro','bg','sk','lt'],true))$lang='da';
        $countries=array_values(array_unique(array_map('strtoupper',array_map('sanitize_key',(array)($a['countries']??[])))));
        $offices=array_values(array_unique(array_map('sanitize_text_field',(array)($a['offices']??[]))));
        $available=get_option('sa_lists',[]);$valid=array_column($available['countries']??[],0);
        if(array_diff($countries,$valid))return new WP_Error('sa_area','Et tildelt land findes ikke i stamdata.',['status'=>422]);
        foreach($offices as $co){$parts=explode('|',$co,2);if(count($parts)!==2||!in_array($parts[1],$available['offices'][$parts[0]]??[],true))return new WP_Error('sa_area','Ugyldig kontortildeling: '.$co,['status'=>422]);}
        return ['name'=>$name,'email'=>$email,'role'=>$role,'lang'=>$lang,'countries'=>$countries,'offices'=>$offices,'disabled'=>!empty($a['disabled'])];
    }
    private static function persist_user($id,$p) {
        $u=get_user_by('id',$id);if(!$u)return new WP_Error('sa_missing','Bruger findes ikke.',['status'=>404]);
        $u->set_role(self::ROLES[$p['role']]);
        wp_update_user(['ID'=>$id,'display_name'=>$p['name'],'user_email'=>$p['email']]);
        update_user_meta($id,'sa_lang',$p['lang']);update_user_meta($id,'sa_countries',$p['countries']);
        update_user_meta($id,'sa_offices',$p['offices']);update_user_meta($id,'sa_disabled',$p['disabled']?'1':'0');
        self::log(0,get_current_user_id(),'user_rights_updated',['user_id'=>$id,'role'=>$p['role'],'countries'=>$p['countries'],'offices'=>$p['offices'],'disabled'=>$p['disabled']]);
        return self::identity(get_user_by('id',$id),true);
    }
    public static function create_user($r) {
        $p=self::user_values($r);if(is_wp_error($p))return $p;
        $username=sanitize_user((string)$r->get_param('username'),true);
        if(!$username||username_exists($username)||email_exists($p['email']))return new WP_Error('sa_exists','Brugernavn eller e-mail er allerede i brug.',['status'=>409]);
        $id=wp_insert_user(['user_login'=>$username,'user_pass'=>wp_generate_password(30,true,true),'user_email'=>$p['email'],'display_name'=>$p['name'],'role'=>self::ROLES[$p['role']]]);
        if(is_wp_error($id))return $id;$result=self::persist_user($id,$p);
        if(function_exists('wp_new_user_notification'))wp_new_user_notification($id,null,'user');
        return $result;
    }
    public static function update_user($r) {
        $id=(int)$r['id'];if($id===get_current_user_id())return new WP_Error('sa_self','Du kan ikke ændre din egen adgang her.',['status'=>422]);
        $u=get_user_by('id',$id);if(!$u)return new WP_Error('sa_missing','Bruger findes ikke.',['status'=>404]);
        if(!in_array(self::role($u),['seller','manager','leadership','admin'],true))return self::forbidden();
        if(in_array('administrator',(array)$u->roles,true))return new WP_Error('sa_wp_admin','WordPress-administratorer administreres separat i WordPress.',['status'=>422]);
        $p=self::user_values($r,$u);if(is_wp_error($p))return $p;
        if($p['role']==='admin'&&self::role($u)==='admin'&&in_array('administrator',(array)$u->roles,true))return new WP_Error('sa_wp_admin','WordPress-administratorer administreres separat i WordPress.',['status'=>422]);
        return self::persist_user($id,$p);
    }
    public static function export_csv() {
        self::log(0,get_current_user_id(),'export_csv',['scope'=>self::role()]);
        $leads=self::fetch_leads(10000);
        $cols=['id','company','vat','country','office','contact','phone','email','owner_id','amount_eur','vehicles','probability','products','status','next_followup','reminder_days','outcome_reason','outcome_note','close_date','notes','created_at','updated_at'];
        $f=fopen('php://temp','w+');fwrite($f,"\xEF\xBB\xBF");fputcsv($f,$cols,';','"','');
        foreach($leads as $lead){$row=[];foreach($cols as $col){$v=$lead[$col]??'';if(is_array($v))$v=implode(', ',$v);$v=(string)$v;if(preg_match('/^[=+@\-\t\r]/u',$v))$v="'".$v;$row[]=$v;}fputcsv($f,$row,';','"','');}
        rewind($f);$csv=stream_get_contents($f);fclose($f);
        return new WP_REST_Response(['_sa_csv'=>$csv],200);
    }
    public static function serve_csv($served,$result,$request,$server) {
        if($request->get_route()!=='/'.self::NS.'/export'||is_wp_error($result))return $served;
        $data=$result->get_data();if(!isset($data['_sa_csv']))return $served;
        header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="salgsadministration.csv"');
        header('Cache-Control: private, no-store, max-age=0');
        echo $data['_sa_csv'];return true;
    }
}
register_activation_hook(__FILE__,['SA_Sales','activate']);
SA_Sales::init();