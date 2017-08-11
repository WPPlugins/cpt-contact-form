<?php
class cpt_contact_form
	{

	function __construct()
		{
		load_plugin_textdomain('cpt-contact-form',false,dirname(plugin_basename(__FILE__)).'/lang/');
		add_action('init',array($this,'define_message_cpt'));
		add_shortcode('cpt-contact-form',array($this,'cpt_contact_form_shortcode'));
		add_action('add_meta_boxes',array($this,'add_meta_boxes'),10,2);
		add_filter("manage_contact-message_posts_columns",array($this,'columns_head'));
		add_action("manage_contact-message_posts_custom_column",array($this,'columns_content'),10,2);
		add_action('admin_menu',array($this,'admin_menu'));
		add_action('wp_ajax_mark_as_read',array($this,'ajax_mark_as_read'));
		add_action('wp_ajax_nopriv_mark_as_read',array($this,'ajax_mark_as_read'));
		add_filter('the_content',array($this,'the_content'));
		add_filter('cron_schedules',array($this,'cron_schedules'));
		add_action('cptcf_notification',array($this,'notify_webmaster'));
		}
		
	function define_message_cpt()
		{
		register_post_type(
			'contact-message',
			array(
				'label'=>__('Contact Messages','cpt-contact-form'),
				'labels'=>array(
					'name'=>__('Contact Messages','cpt-contact-form'),
					'singular_name'=>__('Contact Message','cpt-contact-form'),
					'all_items'=>__('All Contact Messages','cpt-contact-form'),
					'add_new'=>__('New Contact Message','cpt-contact-form'),
					'add_new_item'=>__('Add new Contact Message','cpt-contact-form'),
					'edit_item'=>__('Edit Contact Message','cpt-contact-form'),
					'new_item'=>__('New Contact Message','cpt-contact-form'),
					'view_item'=>__('View Contact Message','cpt-contact-form'),
					'search_items'=>__('Search Contact Messages','cpt-contact-form'),
					'not_found'=>__('No Contact Messages Found','cpt-contact-form'),
					'not_found_in_trash'=>__('No Contact Messages Found in Trash','cpt-contact-form'),
					),
				'description'=>__('Messages from the Contact Form','cpt-contact-form'),
				'public'=>true,
				'menu_icon'=>plugins_url('img/contact_form.png',__FILE__),
				'exclude_from_search'=>true,
//				'publicly_queryable'=>false,
				'supports'=>false,//array('title'),
//				'rewrite' => array("slug"=>'contact-message'),
				)
			);
		}
		
	function cpt_contact_form_shortcode($atts)
		{
		if(isset($_POST['cptcf-submit']))
			if($_POST['yourname']&&$_POST['youremail']&&$_POST['yoursubject']&&$_POST['yourmessage'])
				{
				$msg=$this->save_message($_POST['yourname'],$_POST['youremail'],$_POST['yoursubject'],$_POST['yourmessage']);
				if(!wp_get_schedule('cptcf_notification')) wp_schedule_event(time(),'cptcf-notify','cptcf_notification');
				$r="<p>".__('Your message has been sent. Thank you for your communication.','cpt-contact-form')."</p>";
				}
			else
				$r=$this->render_form(array(__('All fields are mandatory!','cpt-contact-form')));
		else
			$r=$this->render_form();
		return $r;
		}
		
	function render_form($errors=array())
		{
		$yourname=__('Your Name','cpt-contact-form');
		$youremail=__('Your e-mail','cpt-contact-form');
		$yoursubject=__('Subject','cpt-contact-form');
		$yourmessage=__('Message','cpt-contact-form');
		$submit=__('Submit','cpt-contact-form');
		$postedname=(isset($_POST['yourname'])?$_POST['yourname']:'');
		$postedemail=(isset($_POST['youremail'])?$_POST['youremail']:'');
		$postedsubject=(isset($_POST['yoursubject'])?$_POST['yoursubject']:'');
		$postedmessage=(isset($_POST['yourmessage'])?$_POST['yourmessage']:'');
		$errlist='';
		if(count($errors))
			{
			foreach($errors as $error)
				$errlist.="<li>$error</li>";
			$errlist="<ul class='errors'>$errlist</ul>";
			}
		return "
<div class='cptcf'>
	$errlist
	<form method='post'>
		<label>$yourname: <input type='text' name='yourname' value='$postedname'></label>
		<label>$youremail: <input type='text' name='youremail' value='$postedemail'></label>
		<label>$yoursubject: <input type='text' name='yoursubject' value='$postedsubject'></label>
		<label>$yourmessage: <textarea name='yourmessage' cols='45' rows='5'>$postedmessage</textarea></label>
		<span class='submit'><input type='submit' name='cptcf-submit' value='$submit'></span>
	</form>
</div>
		";
		}
		
	function unread_script($id)
		{
		$unread=get_post_meta($id,'unread',true);
		if($unread)
			{
			wp_enqueue_script(
				'cptcf-mark-read',
				plugins_url('js/mark-read.js',__FILE__),
				array('jquery')
				);
			wp_localize_script(
				'cptcf-mark-read',
				'cptcf',
				array(
					'ajaxurl'=>admin_url('admin-ajax.php'),
					'markRead'=>get_option('cptcf_mark_read','explicit'),
					'markReadLiteral'=>__('Mark as Read','cpt-contact-form'),
					'readAfter'=>get_option('cptcf_read_after',10),
					'messageId'=>$id,
					)
				);
			}
		return $unread;
		}
		
	function add_meta_boxes($post_type,$post)
		{
		add_meta_box("contact-message-main-meta",__("Contact Message",'cpt-contact-form'),array($this,"main_metabox"),'contact-message',"normal","high");
		}
		
	function main_metabox($post)
		{
		$email=get_post_meta($post->ID,'email',true);
		$name=get_post_meta($post->ID,'name',true);
		$mailto_url="$email";
		$unread=$this->unread_script($post->ID);
		?>
		<p><?php _e('From','cpt-contact-form'); ?>: <a href="mailto:<?php echo $mailto_url ?>"><?php echo $name; ?></a></p>
		<h3><?php _e('Subject','cpt-contact-form'); ?>: <?php echo $post->post_title; ?></h3>
		<blockquote><?php echo nl2br($post->post_content); ?></blockquote>
		<div class='mark-read-container'></div>
		<?php
		}
		
	function columns_head($columns)
		{
		$columns['from']=__('From','cpt-contact-form');
		$columns['status']=__('Status','cpt-contact-form');
		return $columns;
		}
		
	function columns_content($column,$id)
		{
		switch($column)
			{
			case 'from':
				$email=get_post_meta($id,'email',true);
				$name=get_post_meta($id,'name',true);
				?>
				<p><a href="mailto:<?php echo $email; ?>"><?php echo $name; ?></a></p>
				<?php
				break;
			case 'status':
				$unread=get_post_meta($id,'unread',true);
				?>
				<p><?php echo ($unread?__('Unread','cpt-contact-form'):__('Read','cpt-contact-form')); ?></p>
				<?php
				break;
			}
		}
	
	// admin menu
	
	function admin_menu()
		{
	  add_options_page(
			__('CPT Contact Form Options','cpt-contact-form'),
			__('CPT Contact Form','cpt-contact-form'),
			'manage_options',
			'cptcf-options-menu',
			array($this,'plugin_settings')
			);
		}
		
	function plugin_settings()
		{
		if(isset($_POST['mark_read']))
			{
			$mark_read=$_POST['mark_read'];
			update_option('cptcf_mark_read',$mark_read);
			}
		else
			$mark_read=get_option('cptcf_mark_read','explicit');
		if(isset($_POST['read_after']))
			{
			$read_after=$_POST['read_after'];
			update_option('cptcf_read_after',$read_after);
			}
		else
			$read_after=get_option('cptcf_read_after',10);
		if(isset($_POST['notify_every']))
			{
			$notify_every=$_POST['notify_every'];
			update_option('cptcf_notify_every',$notify_every);
			wp_schedule_event(time(),'cptcf-notify','cptcf_notification');
			}
		else
			$notify_every=get_option('cptcf_notify_every',30);
		?>
		<div class='wrap'>
			<h2><?php _e('CPT Contact Form Options','cpt-contact-form'); ?></h2>
			<form method='post'>
				<table class='form-table'>
					<tr>
						<th scope='row'>
							<?php _e('Mark message as "read"','cpt-contact-form'); ?>
						</th>
						<td>
							<p>
								<label>
									<input type='radio' name='mark_read' value="explicit"<?php echo ("explicit"==$mark_read?' checked="checked"':''); ?>>
									<?php _e('Explicitly, from the edit screen','cpt-contact-form'); ?>
								</label>
							</p>
							<p>
								<label>
									<input type='radio' name='mark_read' value="implicit"<?php echo ("implicit"==$mark_read?' checked="checked"':''); ?>>
									<?php _e('Implicitly, by viewing/editing','cpt-contact-form'); ?> 
								</label>
								<label>
									<?php _e('for more than','cpt-contact-form'); ?>
									<input type='number' id='read_after' name='read_after' value="<?php echo $read_after; ?>">
									<?php _e('seconds','cpt-contact-form'); ?>
								</label>
							</p>
						</td>
					</tr>
					<tr>
						<th scope='row'>
							<?php _e('Notifications','cpt-contact-form'); ?>
						</th>
						<td>
							<label>
								<?php _e('Send a notification e-mail every','cpt-contact-form'); ?>
								<input type='number' id='notify_every' name='notify_every' value="<?php echo $notify_every; ?>">
								<?php _e('minutes','cpt-contact-form'); ?>
							</label>
							<p class="description"><em>(<?php _e('Only if there are new unread messages','cpt-contact-form'); ?>)</em></p>
						</td>
					</tr>
				</table>
				<p class='submit'>
					<input type='submit' name='submit' class="button button-primary" value="<?php _e('Save Changes','cpt-contact-form'); ?>">
				</p>
			</form>
		</div>
		<?php
		}
		
	// cron
	
	function cron_schedules($schedules)
		{
		$schedules['cptcf-notify']=array(
			'interval'=>60*get_option('cptcf_notify_every',30),
			'display'=>__('CPT Contact Form Notification Interval','cpt-contact-form'),
			);
		return $schedules;
		}
		
	// messages CRUD
	
	function save_message($name,$email,$subject,$message)
		{
		$id=wp_insert_post(
			array(
				'post_type'=>'contact-message',
				'post_title'=>$subject,
				'post_content'=>esc_attr(strip_shortcodes($message)),
				'post_status'=>'private',
				)
			);
		update_post_meta($id,'email',$email);
		update_post_meta($id,'name',$name);
		update_post_meta($id,'unread',true);
		return get_post($id);
		}
		
	function notify_webmaster($message)
		{
		$last=get_option('cptcf-last-notification',1);
		$p=get_posts(
			array(
				'post_type'=>'contact-message',
				'post_status'=>array('publish','private'),
				'meta_key'=>'unread',
				'meta_value'=>true,
				'posts_per_page'=>-1,
				'date_query'=>array(array('after'=>$last)),
				)
			);
		$n=count($p);
		if($n)
			{
			// get the admin account's email address
			$to=get_option('admin_email'); 
			$subject=__('CPT Contact Form Notification','cpt-contact-form');
			$body=__("There are $n unread contact messages for you.",'cpt-contact-form');
			$headers="\r\n";
//			$headers="From: $to <$to>\r\n";
			mail($to,$subject,$body,$headers);
			update_option('cptcf-last-notification',current_time('mysql'));
			}
		}
		
	function mark_as_read($id) { delete_post_meta($id,'unread'); }
	
	// front end
	
	function the_content($content)
		{
		global $post;
		if(is_single()&&'contact-message'==$post->post_type)
			{
			$unread=$this->unread_script($post->ID);
//			if($unread) $content="$content<p>(Unread)</p>";
			}
		return $content;
		}

	// ajax
	
	function ajax_mark_as_read()
		{
		if(isset($_POST['message'])) 
			{
			$this->mark_as_read($_POST['message']);
			echo $_POST['message'];
			}
		exit;
		}
		
	}
