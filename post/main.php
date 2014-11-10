<?php
include "../wp-config.php"; //这里是引用原来的数据库文件.
include "./config.php"; 
if( !class_exists("MySql") )	require_once ("mysql-class.php");
if($pingAfterPost) require_once("../wp-includes/comment.php");
if( !class_exists("Snoopy") )	require_once ("../wp-includes/class-snoopy.php");
function hm_tranlate($text){
	$snoopy = new Snoopy;
	$url = "http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q=".urlencode($text)."&langpair=zh-CN%7Cen";
	$submit_vars["text"] = $text;
	$submit_vars["ie"] = "UTF8"; 
	$submit_vars["hl"] = "zh-CN"; 
	$submit_vars["langpair"] = "zh|en"; 
	$snoopy->submit($url,$submit_vars);
	$htmlret = $snoopy->results;
	$htmlret = explode('translatedText',$htmlret);
	$htmlret = explode('}',$htmlret[1]);
	$htmlret = $htmlret[0];
	$htmlret = str_replace('"','',$htmlret);
	$htmlret = str_replace(':','',$htmlret);
	return $htmlret;
}

$DB = new MySql(DB_HOST, DB_USER, DB_PASSWORD,DB_NAME);//初始化数据库类

if(isset($_GET['action'])&&$_GET['action'] == "list")
{
	$sql="SELECT tt.term_id,tt.term_taxonomy_id,t.name,tt.term_id,t.term_id,tt.taxonomy from ".$table_prefix."terms t,".$table_prefix."term_taxonomy tt where t.term_id=tt.term_id AND tt.taxonomy='category' ";
	$query=$DB->query($sql);
	while ($config=$DB->fetch_array($query))
	{
	echo '<<<'.$config['term_id'].'--'.$config['name'].'>>>';	
	}
}
elseif(isset($_GET['action'])&&$_GET['action'] == "save" /*&&isset($_GET['secret'])&&$_GET['secret'] == $secretWord*/)
{
	$comment_count=0;
	$menu_order=0;
	$tag_count=0;	
	$post=$_POST;  
	extract($post);
	if($post_title=='[标签:标题]'||$post_title==''){die('Failure: title is empty');}else{$post_title=trim($post_title);};
	if($post_content=='[标签:内容]'||$post_content==''){die('Failure: content is empty');};
	if($post_category =='[分类id]'|| $post_category==''){$post_category=0;};
	if($tag=='[标签:SY_tag]'){$tag='';}
	$tag=str_replace("|||",",",$tag);
	
	$post_name=$post_title;
	if($translateSlug) $post_name=hm_tranlate($post_name);
	$post_name=sanitize_title( $post_name);
	if( strlen($post_name) < 2 ) $post_name="";
	
	$tm=time()+$randomPostTime+$timeZoneOffset*3600; 
	$post_date=date("Y-m-d H:i:s",$tm);
	$post_status=$postStatus; 
	$sql="INSERT INTO `".$table_prefix."posts` ( `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`) VALUES ($postAuthor, '$post_date', '$post_date', '$post_content', '$post_title','$post_excerpt', '$post_status', 'open', 'open', '', '$post_name', '', '', '$post_date', '$post_date', '$post_content_filtered', 0, '$guid', '$menu_order', 'post', '$post_mime_type', '$comment_count')";
	$query=$DB->query($sql);
	$postid=$DB->insert_id($sql);
	$tm2=$tm+10;
	$sqledit="INSERT INTO `".$table_prefix."postmeta` (post_id ,meta_key ,meta_value ) VALUES ($postid,'_edit_lock','$tm2'),($postid,'_edit_last',1)";
	$query2=$DB->query($sqledit);
 
    $post_category_list= array_unique(explode(",",$post_category));
	foreach($post_category_list as $post_category)
	{
        $cid=1;
        $getcatid="SELECT term_taxonomy_id,term_id from ".$table_prefix."term_taxonomy where term_id='$post_category' and taxonomy='category'";
        $gettag=$DB->fetch_one_array($getcatid);
        if($gettag) $cid=$gettag['term_taxonomy_id'];
        $sqlcid="INSERT INTO `".$table_prefix."term_relationships` (object_id ,term_taxonomy_id ) VALUES ($postid,$cid)";
        $cidquery=$DB->query($sqlcid);
    }

	if(!$tag=='')
	{
	    $tags= array_unique(explode(",",$tag));
		foreach($tags as $var)
		{
			$var=trim($var,'  ');
			if(strlen($var)<2) continue;
			$uc=strtolower(sanitize_title($var));
			//$ssql="SELECT * from ".$table_prefix."terms where name='$var' ";
			$ssql="SELECT * from ".$table_prefix."terms where slug='$uc' ";
			$squery=$DB->fetch_one_array($ssql);
			if($squery)
			{
				$tagidss=$DB->fetch_one_array($ssql);
				$tagids=$tagidss['term_id'];
			}else{
				$addtag="INSERT INTO `".$table_prefix."terms` (name,slug) VALUES ('$var','$uc')";
				$addquery=$DB->query($addtag);
				$tagids=$DB->insert_id($addtag);
			}
			
			$gettagid="SELECT term_taxonomy_id,term_id from ".$table_prefix."term_taxonomy where term_id='$tagids' and taxonomy='post_tag'";
			$gettag=$DB->fetch_one_array($gettagid);
			if($gettag)
			{
				$tagid=$gettag['term_taxonomy_id'];
			}else{
				$addterm="INSERT INTO `".$table_prefix."term_taxonomy` (term_id,taxonomy) VALUES ('$tagids','post_tag')";
				$termquery=$DB->query($addterm);
				$tagid=$DB->insert_id($addterm);
			}
			
			$checksql="SELECT * from ".$table_prefix."term_relationships where object_id=$postid and term_taxonomy_id='$tagid'";
			$checkrt=$DB->fetch_one_array($checksql);
			if(!$checkrt)
			{
				$sqltag="INSERT INTO `".$table_prefix."term_relationships` (object_id ,term_taxonomy_id ) VALUES ($postid,'$tagid')";
				$tagquery=$DB->query($sqltag);
			}
            
            //update the tags count
   			$hmsql="SELECT count(*) as tagcount from ".$table_prefix."term_relationships where term_taxonomy_id=$tagid";
			$gettagcount=$DB->fetch_one_array($hmsql);
			if($gettagcount)
			{
				$tag_count=$gettagcount['tagcount'];
			}
            $hmsql="UPDATE `".$table_prefix."term_taxonomy` set count=$tag_count where term_taxonomy_id='$tagid'";
            $DB->query($hmsql);      
		}
	}
	if($pingAfterPost)  generic_ping();
	echo 'public success!';
}else{
	echo "Prohibited";
}
?>
