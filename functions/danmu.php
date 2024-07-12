<?php if (!defined('ABSPATH')) {
    die;
}
function danmujs()
{
    wp_enqueue_style('danmu-cs', plugin_dir_url(dirname(__FILE__)) . '/css/zibll_plusgin_css.css', array(), THEME_VERSION, false);
    wp_enqueue_script('danmu-js', plugin_dir_url(dirname(__FILE__)) . '/js/danmu.js', array(), THEME_VERSION, true);
}
function zibll_plugin_danmu()
{
    
    global $wpdb;
    $total_trade = $wpdb->get_results("SELECT * FROM $wpdb->zibpay_order WHERE `status` = 1 ORDER BY $wpdb->zibpay_order . `id` DESC ");
    $returnarray = array();
    $user_vip_name = array(
        'vip_1' => _pz('pay_user_vip_1_name'),
        'vip_2' => _pz('pay_user_vip_2_name'),
    );
    $playtypename = array(
        'upgrade' => '升级了',
        'pay' => '购买了',
        'renew' => '续费了',
    );
    
    foreach ($total_trade as $item) {
        $pay_mark = _pz('pay_mark');
        switch($item->pay_type){
            case 'balance':
                $pay_type= $pay_mark;
                break;
            case 'card_pass':
                $pay_type = '充值卡充值';
                break;
            case 'points':
                $pay_type = '积分';
                break;
            default:
                $pay_type = '金额';
        }
        //金额
        $psy_price = maybe_unserialize($item->pay_detail)[$item->pay_type];
        $product_id = $item->product_id; //vip_1_0_pay
        if ($product_id) {
            if(strstr($product_id, 'points')){
                $psy_content = '花费'.$psy_price.$pay_type.'充值了积分';
            }else{
                $playtype = substr($product_id, strripos($product_id, "_") + 1);
                $playtype = $playtypename[$playtype];
                $vipname = substr($product_id, 0, strrpos($product_id, "_") - 2);
                $vipname = $user_vip_name[$vipname];
                $psy_content = '<span>'.$psy_price.$pay_type.'</span>'.$playtype . '&nbsp' . $vipname;
            }
        } elseif (!empty($item->post_id)) {
            if (!get_permalink($item->post_id)) {
                $psy_content = '已删除资源';
            } else {
                $psy_content = '<a class="altitle" target="_blank" href="' . get_permalink($item->post_id) . '" style="color: #fff;">' . get_the_title($item->post_id) . '</a>';
            }
            $psy_content = '<span>' . $psy_price .$pay_type. '</span>&nbsp购买了&nbsp' . $psy_content;
        }else{
            $psy_content = $pay_type.maybe_unserialize($item->pay_detail)[$item->pay_type].$pay_mark ;
        }

        $arr = array(
            "type" => "comment",
            "now_user_link" => zib_get_user_home_url($item->user_id),
            "t" => strtotime($item->create_time),
            "avatar" => plugin_zib_get_data_avatar($item->user_id),
            "content" =>  $psy_content
        );
        array_push($returnarray, $arr);
    }
    header( "Content-Type: application/json" );
    echo json_encode($returnarray);
    exit;
}
if (_pluginpz('zibll_plugin_danmu') && !wp_is_mobile()) {
    add_action('wp_enqueue_scripts', 'danmujs');
    add_action('wp_ajax_nopriv_ziblls_plugin_danmu', 'zibll_plugin_danmu');
    add_action('wp_ajax_ziblls_plugin_danmu', 'zibll_plugin_danmu');
}
