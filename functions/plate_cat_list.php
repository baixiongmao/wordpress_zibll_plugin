<?php if (!defined('ABSPATH')) {
    die;
}
require_once(plugin_dir_path(__DIR__) . '/tool/permission.php');
function ZibllPluginPlates($request)
{
    $args = array(
        'taxonomy' => 'plate_cat',
        'hide_empty' => false,
    );
    $plateinfoarray = get_categories($args);
    if ($plateinfoarray) {
        $plate_cat_list = array();
        foreach ($plateinfoarray as $plate_cat_info) {
            $plate_cat_info_list = [
                'name' => $plate_cat_info->name,
                'platelist' => []
            ];
           
            $plate_cat_id = $plate_cat_info->term_id;
            $Query = array(
                'post_type' => 'plate',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'plate_cat',
                        'field'    => 'id',
                        'terms'    => $plate_cat_id,
                    )
                ),
            );
            $platelistarray = new WP_Query($Query);
            if ($platelistarray->have_posts()) {
                $plate_cat_info_list['platelist'] = [];
                foreach ($platelistarray->posts as $plateinfo) {
                    $platelistarrayinfo = array(
                        'id' => $plateinfo->ID,
                        'name' => $plateinfo->post_title,
                    );
                    array_push($plate_cat_info_list['platelist'], $platelistarrayinfo);
                }
            }
            array_push($plate_cat_list, $plate_cat_info_list);
        }
    }
echo json_encode($plate_cat_list
,JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
) ;
}
function ZibllPluginPlatesApi()
{
    register_rest_route('zibll_plug/v1', 'plates', [
        'methods' => 'get',
        'callback' => 'ZibllPluginPlates',
        'permission_callback' => 'ZibllPluginPermission'
    ]);
};
