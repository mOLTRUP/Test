<?php
/**
 * Lightweight business-logic test suite. WordPress functions are stubs; this is
 * NOT a full integration test on a live WordPress installation.
 */
declare(strict_types=1);
define('ABSPATH', __DIR__ . '/');

class WP_Error {
    public string $code;
    public string $message;
    public array $data;
    public function __construct(string $code, string $message, array $data = []) {
        $this->code = $code; $this->message = $message; $this->data = $data;
    }
}
class WP_User {
    public int $ID; public array $roles; public string $display_name;
    public string $user_email = 'test@example.com';
    public function __construct(int $id, array $roles, string $name) {
        $this->ID = $id; $this->roles = $roles; $this->display_name = $name;
    }
    public function exists(): bool { return true; }
}
class FakeDb {
    public string $prefix = 'wp_';
    public mixed $duplicate = null;
    public function get_var($query) { return $this->duplicate; }
    public function prepare($query, ...$args) { return $query; }
}
class Req {
    private array $data;
    public function __construct(array $data) { $this->data = $data; }
    public function get_json_params(): array { return $this->data; }
}
function add_action(...$a) {}
function add_filter(...$a) {}
function add_shortcode(...$a) {}
function register_activation_hook(...$a) {}
function wp_get_current_user() { return $GLOBALS['current_user']; }
function get_current_user_id(): int { return $GLOBALS['current_user']->ID; }
function get_user_by($key, $id) { return $GLOBALS['users'][(int)$id] ?? false; }
function get_user_meta($id, $key, $single = true) { return $GLOBALS['metadata'][$id][$key] ?? null; }
function get_option($key, $default = false) { return $GLOBALS['options'][$key] ?? $default; }
function sanitize_text_field($v): string { return trim(strip_tags((string)$v)); }
function sanitize_textarea_field($v): string { return trim(strip_tags((string)$v)); }
function esc_url_raw($v): string { return trim((string)$v); }
function absint($v): int { return abs((int)$v); }
function sanitize_email($s): string { return trim((string)$s); }
function is_email($s): bool { return (bool)filter_var($s, FILTER_VALIDATE_EMAIL); }
function wp_json_encode($data, ...$rest): string { return json_encode($data, JSON_UNESCAPED_UNICODE); }
function wp_timezone(): DateTimeZone { return new DateTimeZone('Europe/Copenhagen'); }
function current_time($fmt): string { return (new DateTimeImmutable('now',wp_timezone()))->format($fmt); }
function is_wp_error($obj): bool { return $obj instanceof WP_Error; }

$GLOBALS['wpdb'] = new FakeDb();
$GLOBALS['users'] = [
    1 => new WP_User(1,['sa_seller'],'Sælger A'),
    2 => new WP_User(2,['sa_seller'],'Sælger B'),
    3 => new WP_User(3,['sa_manager'],'Landechef'),
    4 => new WP_User(4,['sa_leadership'],'Ledelse'),
    5 => new WP_User(5,['sa_administrator'],'Admin')
];
$GLOBALS['metadata'] = [
    1 => ['sa_countries'=>['DK'],'sa_offices'=>['DK|Padborg']],
    2 => ['sa_countries'=>['DE'],'sa_offices'=>['DE|Berlin']],
    3 => ['sa_countries'=>['DK'],'sa_offices'=>['DE|Hamborg']]
];
$GLOBALS['options'] = ['sa_lists'=>[
    'countries'=>[['DK','Danmark'],['DE','Tyskland']],
    'offices'=>['DK'=>['Padborg','København'],'DE'=>['Hamborg','Berlin']],
    'products'=>['Moms','Vejskat','Færge'],
    'outcomeReasons'=>['won'=>['Produktmatch','Andet'],'lost'=>['Pris','Andet']]
]];
$GLOBALS['current_user'] = $GLOBALS['users'][1];

require __DIR__ . '/../plugin/salgsadministration.php';
$reflection = new ReflectionClass('SA_Sales');
function invokeSA(string $method, ...$args) {
    global $reflection;
    return $reflection->getMethod($method)->invoke(null, ...$args);
}
$checks = 0;
function check($condition, string $what): void {
    global $checks; $checks++;
    if (!$condition) throw new RuntimeException('FAIL: ' . $what);
}
function selectUser(int $id): void { $GLOBALS['current_user'] = $GLOBALS['users'][$id]; }

$a = ['owner_id'=>1,'country'=>'DK','office'=>'Padborg'];
$b = ['owner_id'=>2,'country'=>'DE','office'=>'Berlin'];
$hamburg = ['owner_id'=>2,'country'=>'DE','office'=>'Hamborg'];
$copenhagen = ['owner_id'=>2,'country'=>'DK','office'=>'København'];

selectUser(1);
check(invokeSA('can_read',$a) === true,'seller reads own lead');
check(invokeSA('can_read',$b) === false,'seller cannot read another lead');
check(invokeSA('can_write',$b) === false,'seller cannot write another lead');
check(invokeSA('scoped_where')[0] === 'owner_id=%d','seller SQL is owner-filtered');

selectUser(3);
check(invokeSA('can_read',$copenhagen) === true,'manager sees assigned whole country');
check(invokeSA('can_read',$hamburg) === true,'manager sees assigned specific office');
check(invokeSA('can_read',$b) === false,'manager cannot read unauthorized office');
check(str_contains(invokeSA('scoped_where')[0],' OR '),'manager SQL reflects country/office union');

selectUser(4);
check(invokeSA('can_read',$b) === true,'leadership reads globally');
check(invokeSA('can_write',$b) === false,'leadership remains read-only');
selectUser(5);
check(invokeSA('can_write',$b) === true,'administrator can write globally');

$future=(new DateTimeImmutable('tomorrow',wp_timezone()))->format('Y-m-d') . 'T12:00';
$base = [
    'company'=>'Testtransport','vat'=>'DK-TEST-9000','country'=>'DK','office'=>'Padborg',
    'contact'=>'Kontakt','phone'=>'+45 12345678','email'=>'kontakt@example.com',
    'owner_id'=>1,'amount_eur'=>100000,'vehicles'=>20,'probability'=>50,
    'products'=>['Moms','Vejskat'],'status'=>'dialog','next_followup'=>$future,
    'reminder_days'=>2,'notes'=>'Fiktiv test'
];
selectUser(1);
$good=invokeSA('validate',new Req($base));
check(!is_wp_error($good) && $good['amount_eur']===100000.0,'valid active lead passes');
check($good['next_followup'] === str_replace('T',' ',$future).':00','datetime is stored correctly');

$bad=$base;$bad['email']='not-an-email';
check(invokeSA('validate',new Req($bad))->code==='sa_email','invalid email is rejected');
$bad=$base;$bad['office']='Berlin';
check(invokeSA('validate',new Req($bad))->code==='sa_area','office must match country');
$bad=$base;$bad['next_followup']='';
check(invokeSA('validate',new Req($bad))->code==='sa_followup','active lead requires follow-up');
$bad=$base;$bad['vehicles']=-4;
check(invokeSA('validate',new Req($bad))->code==='sa_validation','negative vehicle count rejected');
$bad=$base;$bad['owner_id']=2;
check(invokeSA('validate',new Req($bad))->code==='sa_forbidden','seller cannot assign to another seller');

$won=$base;$won['status']='won';$won['close_date']=current_time('Y-m-d');$won['next_followup']='';$won['outcome_reason']='Produktmatch';
check(!is_wp_error(invokeSA('validate',new Req($won))),'valid won lead passes');
$bad=$won;$bad['outcome_reason']='Pris';
check(invokeSA('validate',new Req($bad))->code==='sa_outcome_invalid','won reason cannot use lost-only reason');
$bad=$won;$bad['outcome_reason']='Andet';$bad['outcome_note']='';
check(invokeSA('validate',new Req($bad))->code==='sa_outcome_note','Other requires note');
$bad=$won;$bad['close_date']='';
check(invokeSA('validate',new Req($bad))->code==='sa_outcome','close date required');

$GLOBALS['wpdb']->duplicate=42;
check(invokeSA('validate',new Req($base))->code==='sa_duplicate','duplicate active VAT blocked');
$GLOBALS['wpdb']->duplicate=null;

selectUser(3);
$transfer=$base;$transfer['owner_id']=2;
$before=['id'=>99,'owner_id'=>1,'country'=>'DK','office'=>'Padborg'];
check(invokeSA('validate',new Req($transfer),$before)->code==='sa_transfer','owner change needs reason');
$transfer['transfer_reason']='Aftalt flytning';
check(!is_wp_error(invokeSA('validate',new Req($transfer),$before)),'manager transfers with reason');

echo "PASS PHP business logic: $checks role, scope, validation and outcome checks\n";
