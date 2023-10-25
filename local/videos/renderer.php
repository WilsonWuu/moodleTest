<?php
if (!defined('MOODLE_INTERNAL')) {
	die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/innoverz/lib/outputcomponents.php');

class core_videos_renderer
{

	public function videos_top_navigation($default_data = null)
	{
		global $OUTPUT, $CFG;
		$output = '';
		$output .= '<div class="publicmarginbottom divtable responsive">';
		$output .= '<div class="tablecell">';
		$output .= $this->search_video_search_bar($default_data);
		$output .= '</div>';
		$output .= '<div class="tablecell">';

		if (has_capability('local/videos:managevideoresources', context_system::instance())) {
			$url = new moodle_url($CFG->VIDEOS_BASEURL.'editvideo.php', array('add' => 'resource', 'course' => 1));
			$output .= html_writer_innoverz::new_record_widget($url, get_string('uploadvideo', 'local_videos'));
		}
		$output .= '</div>';
		$output .= '</div>'; // end table
		$output .= '</div>'; // end table
		return $output;
	}

	public function search_video_search_bar($default_data = null)
	{
		global $DB;

		empty_replace($default_data['category'], 0);

		$cats = get_sellector_all_categories();

		$output = '';

		$output .= '<div class="search_video">';
		$output .= '<form autocomplete="off" method="GET" id="category_list">';
		$output .= '<input type="hidden" name="issubmit" />';
		$output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
		$output .= '<label class="removemarginbottom" for="id_category">';
		$output .= '<span>' . get_string('category', 'local_videos') . ': </span>';
		$output .= '<select name="category" id="id_category">';
		$output .= '<option value="0">' . get_string('allcategory', 'local_videos') . '</option>';
		foreach ($cats as $key => $value) {
			$output .= '<option value="' . $key . '"' . ($default_data['category'] == $key ? ' selected="selected"' : '') . '>' . $value . '</option>';
		}
		$output .= '</select>';
		$output .= '</label>';
		//$output .= '<input type="submit" name="btn_sesarchvideo" value="Submit" class="accesshide"/>';
		$output .= '</form>';
		$output .= '</div>';

		return $output;
	}

	public function get_video_player($urls)
	{
		global $OUTPUT;
		$posterurl = $OUTPUT->image_url('transparent_flag','theme');
		$flashplayerhtml = "
			<object id='SampleMediaPlayback1' name='SampleMediaPlayback' class='videoplayer' type='application/x-shockwave-flash' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' >
				<param name='movie' value='swfs/SampleMediaPlayback.swf' /> 
				<param name='wmode' value='opaque' />
				<param name='quality' value='high' /> 
				<param name='bgcolor' value='#000000' /> 
				<param name='allowfullscreen' value='true' /> 
				<param name='flashvars' value= '&src={$urls[1]}'/>
				<embed class='videoplayer' src='swfs/SampleMediaPlayback.swf' quality='high' bgcolor='#000000' id='SampleMediaPlayback2' name='SampleMediaPlayback' allowfullscreen='true' pluginspage='http://www.adobe.com/go/getflashplayer' flashvars='&src={$urls[1]}' type='application/x-shockwave-flash'> </embed>
			</object>
		";
		if (
			isset($_SERVER['HTTP_USER_AGENT']) &&
			(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)
		) {
			// Only use flash player for IE	
			return $flashplayerhtml;
		} else {
			return " 
				<video class='videoplayer' poster='$posterurl' controls>
					<source src='{$urls[0]}' type='video/mp4'>
					$flashplayerhtml
				</video> 
			";
		}
		/*return "
			<object id='SampleMediaPlayback1' name='SampleMediaPlayback' class='videoplayer' type='application/x-shockwave-flash' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' >
						<param name='movie' value='swfs/SampleMediaPlayback.swf' /> 
						<param name='wmode' value='opaque' />
						<param name='quality' value='high' /> 
						<param name='bgcolor' value='#000000' /> 
						<param name='allowfullscreen' value='true' /> 
						<param name='flashvars' value= '&src={$urls[1]}'/>
						<embed class='videoplayer' src='swfs/SampleMediaPlayback.swf' quality='high' bgcolor='#000000' id='SampleMediaPlayback2' name='SampleMediaPlayback' allowfullscreen='true' pluginspage='http://www.adobe.com/go/getflashplayer' flashvars='&src={$urls[1]}' type='application/x-shockwave-flash'> </embed>
					</object>";*/
	}

	public function video_version_control($ishdvideo = 0)
	{
		global $PAGE;
		$action = $PAGE->url;
		$text_button = $ishdvideo ? get_string('sdversion', 'local_videos') : get_string('hdversion', 'local_videos');
		$ishdvideo = !$ishdvideo;
		return " 
				<form action='$action' method='POST' class='publicmargintop'>
					<input type='hidden' name='ishdvideo' value='$ishdvideo'/>
					<input type='submit' value='$text_button'/>
				</form>
		";
	}

	public function view_video_list($video_list = null, $totalcount = 0, $page = 0, $perpage = 10, $url = "", $detail = false)
	{
		global $OUTPUT, $CFG, $USER, $DB;
		$output = '';

		$output .= html_writer_innoverz::table_paging_header($totalcount, $page, $perpage, $url);

		if ($video_list == null || count($video_list) == 0) {
			$output .= '<div class="contentblock list-no-result">' . get_string('videolistnoresult', 'local_videos') . '</div>';
			return $output;
		}

		$output .= '
			<table class="logtable generaltable">
			<caption class="accesshide">Summary</caption>
				<tbody>
		';

		$mobilelayout = '<div class="mobile-table hidden">';

		/*$table = new html_table();
		$table->classes = array('logtable','generaltable');
		$table->align = array('center', 'left', 'right');
		$table->head = array(
				'',
				get_string('detail', 'local_videos'),
				'',			
		);	*/

		$startrow = $page * $perpage;
		$endrow = $startrow + $perpage;
		$endrow = ($endrow > $totalcount) ? $totalcount : $endrow;

		$i = 0;
		foreach ($video_list as $data) {
			if ($i < $startrow) {
				$i++;
				continue;
			}
			if ($i >= $endrow) {
				break;
			}
			$i++;
			//print_r($data); exit();
			// get video thumbnail
			if ($file = get_file_by_course_module($data->id, 'image')) {
				$resource = $DB->get_record('resource', array('id' => $data->instance), '*', MUST_EXIST);
				$context = context_module::instance($data->id);
				$path = '/' . $context->id . '/mod_resource/content/' . $resource->revision . $file->get_filepath() . $file->get_filename();
				$videoimageurl = moodle_url::make_file_url('/pluginfile.php', $path, false);
			} else {
				$videoimageurl = new moodle_url("/local/videos/readimagefile.php?id={$data->id}&sesskey=" . sesskey());
			}

			$fristrowclass = 'fixheight';
			$secondrowclass = 'hidebordertop videodescpadding';
			if ($i % 2 != 0) {
				$fristrowclass .= ' odd';
				$secondrowclass .= ' odd';
			}
			$buttonedithtml = '';
			//if (is_siteadmin() || $USER->id == $data->userid) {
			if (has_capability('local/videos:managevideoresources', context_system::instance()) || $USER->id == $data->userid) {
				$buttonedithtml = '<a href="' . $CFG->wwwroot . $CFG->VIDEOS_BASEURL.'editvideo.php?update=' . $data->id . '">' . get_string('buttonedit', 'local_videos') . '</a>';
				$buttonedithtml .= '&nbsp&nbsp&nbsp<a href="' . $CFG->wwwroot . $CFG->VIDEOS_BASEURL.'searchvideo.php?delete=' . $data->id . '&sesskey=' . sesskey() . '">' . get_string('buttondelete', 'local_videos') . '</a>';
			}
			$deschtml = '';
			if ($data->showdescription) {
				if ($data->introformat == 1) {
					$deschtml .= $data->intro;
				} else {
					$deschtml .= '<p>' . $data->intro . '<br></p>';
				}
			}
			$output .= '
				<tr>
					<td class="' . $fristrowclass . '" rowspan="2" width="15%">' . html_writer::empty_tag('img', array('src' => $videoimageurl, 'alt' => 'Video thumbnail', 'class' => 'videothumbnail')) . '</td>
					<td class="' . $fristrowclass . '" width="77%">' . '<a href="' . new moodle_url($CFG->VIDEOS_BASEURL.'view.php?id=' . $data->id) . '" class="title" target="' . (empty($data->link) ? '_self' : '_blank') . '">' . $data->name . '</a></td>
					<td class="' . $fristrowclass . '" width="8%">' . $buttonedithtml . '</td>
				</tr>
				<tr>
					<td colspan="2" class="' . $secondrowclass . '">' . $deschtml . '</td>
				</tr>
			';

			$mobilelayout .= '
				<div class="mobile-table-row">
					<div class="mobile-table-cell half left">' . html_writer::empty_tag('img', array('src' => $videoimageurl, 'alt' => 'Video thumbnail', 'class' => 'videothumbnail')) . '</div>' .
				'<div class="mobile-table-cell half right"><a href="' . new moodle_url($CFG->VIDEOS_BASEURL.'view.php?id=' . $data->id) . '" class="title" target="' . (empty($data->link) ? '_self' : '_blank') . '">' . $data->name . '</a></div>' .
				'<div class="mobile-table-cell">' . $deschtml . '</div>' .
				'<div class="mobile-table-cell">' . $buttonedithtml . '</div>
				</div>
			';

			//get video thumbnail
			//$filepaths = get_filepath_by_course_module($data->id);
			//echo 'data:;base64,'.base64_encode(file_get_contents($filepaths->image));
		}
		$output .= '</tbody></table>';
		$output .= $mobilelayout . '</div>';
		$output .= $OUTPUT->paging_bar($totalcount, $page, $perpage, "$url");

		return $output;
	}

	public function download_transcript($cmid)
	{
		global $CFG;
		$html = '
			<form method="GET" action="' . $CFG->wwwroot . $CFG->VIDEOS_BASEURL.'view.php" class="btn_download_transcript">
				<input type="hidden" name="id" value="' . $cmid . '"/>
				<input type="hidden" name="download" value="1"/>
				<input type="submit" name="downloadtranscript" value="' . get_string('downloadtranscript', 'local_videos') . '"/>
			</form>
		';
		return $html;
	}

	public function start_layout()
	{
		return html_writer::start_tag('div', array('class' => 'mainvideos'));
	}

	public function complete_layout()
	{
		return html_writer::end_tag('div');
	}
}
