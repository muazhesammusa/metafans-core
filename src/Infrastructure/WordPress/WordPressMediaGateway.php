<?php
/** WordPress media adapter for MetaFans community uploads. */
namespace METAFANSCORE\Infrastructure\WordPress;
use METAFANSCORE\Application\Contracts\MediaGateway;
defined( 'ABSPATH' ) || exit;
final class WordPressMediaGateway implements MediaGateway {
    public function upload( int $actor_id, string $field_name, bool $images_only = false ) {
        if(empty($_FILES[$field_name])||!is_array($_FILES[$field_name])) return new \WP_Error('missing_upload',__('No file was uploaded.','metafans-core'));
        $file=$_FILES[$field_name]; if(empty($file['tmp_name'])||empty($file['name'])||!is_uploaded_file($file['tmp_name'])) return new \WP_Error('invalid_upload',__('Invalid upload.','metafans-core'));
        $max=min(wp_max_upload_size(),(int)apply_filters('metafans_activity_upload_max_bytes',25*MB_IN_BYTES)); if(empty($file['size'])||(int)$file['size']>$max)return new \WP_Error('upload_too_large',__('This file is too large.','metafans-core'));
        $mimes=array('jpg|jpeg|jpe'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','mp4|m4v'=>'video/mp4','webm'=>'video/webm','mov|qt'=>'video/quicktime','pdf'=>'application/pdf','txt|asc|c|cc|h'=>'text/plain','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','ppt'=>'application/vnd.ms-powerpoint','pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation'); $mimes=apply_filters('metafans_activity_upload_mimes',$mimes); if($images_only)$mimes=array_filter($mimes,static fn($mime)=>0===strpos($mime,'image/'));
        $checked=wp_check_filetype_and_ext($file['tmp_name'],$file['name'],$mimes); if(empty($checked['type'])||empty($checked['ext'])) return new \WP_Error('upload_type_not_allowed',__('This file type is not allowed.','metafans-core'));
        if(!function_exists('media_handle_upload')){require_once ABSPATH.'wp-admin/includes/file.php';require_once ABSPATH.'wp-admin/includes/media.php';require_once ABSPATH.'wp-admin/includes/image.php';}
        $id=media_handle_upload($field_name,0,array('post_author'=>$actor_id),array('test_form'=>false,'mimes'=>$mimes)); if(is_wp_error($id))return $id; wp_update_post(array('ID'=>$id,'post_author'=>$actor_id)); update_post_meta($id,'_metafans_community_upload_state','staged'); update_post_meta($id,'_metafans_community_upload_owner',$actor_id); update_post_meta($id,'_metafans_community_upload_staged_at',time()); $url=wp_get_attachment_url($id); $path=get_attached_file($id); $pi=$path?pathinfo($path):array(); return array('id'=>(int)$id,'url'=>esc_url_raw($url),'mime'=>sanitize_mime_type((string)get_post_mime_type($id)),'filename'=>sanitize_file_name($pi['filename']??wp_basename($url)));
    }
    public function can_delete( int $actor_id, int $attachment_id ): bool { $p=get_post($attachment_id); return $p&&'attachment'===$p->post_type&&($actor_id===(int)$p->post_author||user_can($actor_id,'delete_post',$attachment_id)||user_can($actor_id,'bp_moderate')||user_can($actor_id,'manage_options')); }
    public function delete( int $attachment_id ): bool { return (bool) wp_delete_attachment($attachment_id,true); }
    public function mark_attached( int $attachment_id, int $actor_id, int $activity_id ): void {
        if ( $attachment_id < 1 || $actor_id < 1 || $activity_id < 1 ) return;
        $post=get_post($attachment_id);
        $owner=(int)get_post_meta($attachment_id,'_metafans_community_upload_owner',true);
        $state=(string)get_post_meta($attachment_id,'_metafans_community_upload_state',true);
        if(!$post||'attachment'!==$post->post_type||(int)$post->post_author!==$actor_id||$owner!==$actor_id||'staged'!==$state)return;
        update_post_meta($attachment_id,'_metafans_community_upload_state','attached');
        update_post_meta($attachment_id,'_metafans_community_activity_id',$activity_id);
        delete_post_meta($attachment_id,'_metafans_community_upload_staged_at');
    }
    public function cleanup_orphans( int $older_than, int $limit = 50 ): int {
        $ids=get_posts(array(
            'post_type'=>'attachment',
            'post_status'=>'inherit',
            'posts_per_page'=>max(1,min(100,$limit)),
            'fields'=>'ids',
            'no_found_rows'=>true,
            'meta_query'=>array(
                'relation'=>'AND',
                array('key'=>'_metafans_community_upload_state','value'=>'staged'),
                array('key'=>'_metafans_community_upload_staged_at','value'=>$older_than,'compare'=>'<=','type'=>'NUMERIC'),
                array('key'=>'_metafans_community_activity_id','compare'=>'NOT EXISTS'),
            ),
        ));
        $deleted=0;
        foreach((array)$ids as $id){
            $id=(int)$id;
            if('staged'!==get_post_meta($id,'_metafans_community_upload_state',true))continue;
            if((int)get_post_meta($id,'_metafans_community_activity_id',true)>0)continue;
            if(wp_delete_attachment($id,true))$deleted++;
        }
        return $deleted;
    }
}
