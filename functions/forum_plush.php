<?php if (!defined('ABSPATH')) {
    die;
}
function ZibllPluginForumPlus($request)
{
    $platecatid = $request['platecat'];
    $password = $request['password'];
    $ty = $request['pand'];
    return $ty['na'];
    if(empty($password)||$password != _pluginpz('plush_password')){
        return new WP_Error('400', esc_html__('密码错误', 'text_domain'), array('status' => 400));
    }
    $title = $request['title'];
    if (empty($title)) {
        return new WP_Error('400', esc_html__('没有标题', 'text_domain'), array('status' => 400));
    }
    $content = $request['content'];
    $topic = $request['topic'];
    if (!empty($topic)) {
        $topic = explode(',', $topic);
    }
    $tag = $request['tag'];
    if (!empty($tag)) {
        $tag = explode(',', $tag);
    }
    $userid = $request['userid'];
    if(empty($userid)){
        $userid = empty(_pluginpz('plush_userid'))?1:_pluginpz('plush_userid');
    }
    $post_status = _pluginpz('plush_post_status');
    $post_content = fileHandle('fujian',$content);
    $insert_args = array(
        'post_type'      => 'forum_post',
        'post_author'      => $userid,
        'post_title'     => $title,
        'post_status'    => $post_status,
        'post_content'   => $post_content,
        'comment_status' => 'open',
        'meta_input'     => array(
            'plate_id' => $platecatid,
        ),
    );
    $insert_id = wp_insert_post($insert_args, true);

    if (is_wp_error($insert_id)) {
        echo '发布失败';
    } else {
        wp_set_post_terms($insert_id, $topic, 'forum_topic');
        wp_set_post_terms($insert_id, $tag, 'forum_tag');
        echo '成功';
    }
}

// 偷来的代码
// ◕‿◕
function fileHandle($filesnames, $content = null)
{
    global $thumbid;
    if (!empty($_FILES[$filesnames.'0']['name'])) {
        require_once('./wp-load.php');
        require_once('./wp-admin/includes/file.php');
        require_once('./wp-admin/includes/image.php');
        $i = 0;
        while (isset($_FILES[$filesnames.$i])) {
            $fujian[$i] = $_FILES[$filesnames.$i];
            $filename = $fujian[$i]['name'];
            $fileExt=array_pop(explode(".", $filename));
            $upFileTime=date("YmdHis");
            $fujian[$i]['name'] = $upFileTime."-".uniqid().".".$fileExt;
            $uploaded_file = wp_handle_upload($fujian[$i], array('test_form' => false));
            $content = str_replace("\'".$filename."\'", "\"".$uploaded_file['url']."\"", $content);
            $content = str_replace($filename, $uploaded_file['url'], $content);
            if (isset($uploaded_file['error'])) {
                echo "文件上传失败";
                wp_die($uploaded_file['error']);
            }
            $file = $uploaded_file['file'];
            $new_file = iconv('GBK', 'UTF-8', $file);
            $url = iconv('GBK', 'UTF-8', $uploaded_file['url']);
            $type = $uploaded_file['type'];
            $attachment = array(
                'guid' => $url,
                'post_mime_type' => $type,
                'post_title' => $filename,
                'post_content' => '',
                'post_status' => 'inherit'
                );
            $attach_id = wp_insert_attachment($attachment, $new_file);
            if (strpos($fujian[$i]['type'], 'image') !== false) {
                if(empty($thumbid) || $filesnames == 'thumb') $thumbid = $attach_id;
                $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                $attach_data['file'] = iconv('GBK', 'UTF-8', $attach_data['file']);
                foreach ($attach_data['sizes'] as $key => $sizes) {
                    $sizes['file'] = iconv('GBK', 'UTF-8', $sizes['file']);
                    $attach_data['sizes'][$key]['file'] = $sizes['file'];
                }
            wp_update_attachment_metadata($attach_id, $attach_data);
            }
            $i++;
        }
    }
    return $content;
}

function ZibllPluginForumPlushApi()
{
    register_rest_route('zibll_plug/v1', 'forumplush', [
        'methods' => 'post',
        'callback' => 'ZibllPluginForumPlus',
        'permission_callback' => 'ZibllPluginPermission'
    ]);
};
if (_pluginpz('plush_switch')) {
    add_filter('rest_api_init', 'ZibllPluginPlatesApi');
    add_filter('rest_api_init', 'ZibllPluginForumPlushApi');
}
?>
