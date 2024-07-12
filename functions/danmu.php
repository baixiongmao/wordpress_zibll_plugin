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
    $db_name = $wpdb->prefix . 'zibpay_order';
    // 获取数量
    $danmu_count = _pluginpz('danmu_count');
    $total_trade = $wpdb->get_results("SELECT * FROM $db_name WHERE `status` = 1 ORDER BY $db_name . `id` DESC LIMIT 0,$danmu_count");
    // 转ZibllOrder对象
    $danmu_list = array();
    // 货币符号
    $currency_symbol = zibpay_get_pay_mark();
    foreach ($total_trade as $value) {
        array_push($danmu_list, danmu_array($value, $currency_symbol));
    }
    $damu_obj = array(
        'color' => _pluginpz('danmu_bgcolor'),
        'danmus' => $danmu_list,
    );
    header("Content-Type: application/json");
    echo json_encode($damu_obj);
    exit;
}

function danmu_array($order, $mark)
{
    // $shop_type = zibpay_get_pay_type_name($order->order_type);
    $userid = $order->user_id;
    // 金额
    $price = $order->order_price;
    $avatar = plugin_zib_get_data_avatar($userid);
    $content = '<span>' . zibll_plugin_pay_type($order->pay_type) . zibll_plugin_price($price, $mark) . '</span>' . zibll_plugin_order_type_name($order->order_type, $order->post_id) . product_name($order->product_id);
    return array(
        'avatar' => $avatar,
        'content' => $content,
        'time' => $order->pay_time,
        'color' => '',
        "type" => "comment",
    );
}

function zibll_plugin_pay_type($paytype)
{
    switch ($paytype) {
        case 'balance':
            $mark = '使用余额';
            break;
        case 'card_pass':
            $mark = '使用充值卡充值';
            break;
        case 'points':
            $mark = '使用积分';
            break;
        default:
            $mark = '支付';
    }
    return $mark;
}

function zibll_plugin_price($price, $mark)
{
    if ($price > 0) {
        return $price . $mark;
    } else {
        return '';
    }
}

/**
 * 订单类型名称
 * @param [type] $order_type 订单类型
 * @return string
 */
function zibll_plugin_order_type_name($order_type, $postid)
{
    if ($postid) {
        $post = '<a class="altitle" target="_blank" href="' . get_permalink($postid) . '">' . get_the_title($postid) . '</a>';
    }
    $name = array(
        '1' => '&nbsp阅读&nbsp' . $post,
        '2' => '&nbsp购买&nbsp' . $post,
        '3' => '产品购买',
        '5' => '付费图片',
        '6' => '付费视频',
        '7' => '自动售卡',
    );
    return $name[$order_type] ?? '';
}

function product_name($productid)
{
    $vip1name = _pz('pay_user_vip_1_name');
    $vip2name = _pz('pay_user_vip_2_name');

    $name = array(
        'vip_1_0_pay' => '开通' . $vip1name,
        'vip_2_0_pay' => '开通' . $vip2name,
        'vip_1_1_renew' => '续费' . $vip1name,
        'vip_2_1_upgrade' => '升级到' . $vip2name,
        'vip_2_1_renew' => '续费' . $vip2name,
        'points_0' => '充值积分',
        'exchange_3' => '兑换积分'
    );
    return $name[$productid] ?? '';
}

function zibll_plugin_danmu_ajax_js()
{
?>
    <script type="text/javascript">
        // 使用jQuery的$(document).ready()确保在DOM加载完成后执行
        (function($) {
            $(document).ready(function() {
                // 页面加载完成后的代码
                $.ajax({
                    type: "POST",
                    url: '/wp-admin/admin-ajax.php',
                    data: {
                        action: 'ziblls_plugin_danmu'
                    },
                    success: function(msg) {
                        var Obj = $('body').barrage({
                            data: msg,
                            row: <?php echo intval(_pluginpz('danmu_row')); ?>, // 显示行数
                            time: <?php echo intval(_pluginpz('danmu_time')); ?>, // 时间间隔
                            gap: <?php echo intval(_pluginpz('danmu_gap')); ?>, // 间隙
                            ismoseoverclose: <?php echo intval(_pluginpz('danmu_ismoseoverclose')); ?> // 悬浮是否停止
                        });

                        if ($('#zipllplugin-danmu').length == 0) {
                            Obj.start();
                        }
                    }
                });
            });
        })(jQuery);
    </script>
<?php
}

// 输出css代码
function zibll_plugin_danmu_css()
{ ?>
    <style>
        #zipllplugin-danmu {
            right: <?php echo _pluginpz('danmu_right_margin') ?>px;
        }
    </style>
<?php
}

function add_js_to_frontend_footer()
{
    if (!is_admin()) {
        add_action('wp_footer', 'zibll_plugin_danmu_ajax_js');
        add_action('wp_footer', 'zibll_plugin_danmu_css');
    }
}


if (_pluginpz('zibll_plugin_danmu') && !wp_is_mobile()) {
    add_js_to_frontend_footer();
    add_action('wp_enqueue_scripts', 'danmujs');
    add_action('wp_ajax_nopriv_ziblls_plugin_danmu', 'zibll_plugin_danmu');
    add_action('wp_ajax_ziblls_plugin_danmu', 'zibll_plugin_danmu');
}
