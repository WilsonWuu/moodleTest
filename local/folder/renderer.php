<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/innoverz/lib/weblib.php');

require_once($CFG->dirroot . '/mod/folder/renderer.php');

class local_folder_renderer extends mod_folder_renderer
{
    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     */
    protected function htmllize_tree($tree, $dir)
    {
        global $CFG;

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        $result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(24), $subdir['dirname'], 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')) .
                html_writer::tag('span', s($subdir['dirname']), array('class' => 'fp-filename'));
            $filename = html_writer::tag('div', $filename, array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename . $this->htmllize_tree($tree, $subdir));
        }
        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            if ($file->get_mimetype() == 'video/mp4') {
                $url = moodle_url_innoverz::make_pluginfile_url_stream_videos_in_folder(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $filename,
                    false
                );
                $link_param = array('href' => $url);
            } else {
                $url = moodle_url_innoverz::make_pluginfile_inline_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $filename,
                    false
                );
                $link_param = array('href' => $url, 'target' => '_blank');
            }
            $filenamedisplay = clean_filename($filename);
            if (file_extension_in_typegroup($filename, 'web_image')) {
                $image = $url->out(false, array('preview' => 'tinyicon', 'oid' => $file->get_timemodified()));
                $image = html_writer::empty_tag('img', array('src' => $image));
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $filenamedisplay, 'moodle');
            }
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')) .
                html_writer::tag('span', $filenamedisplay, array('class' => 'fp-filename'));
            $filename = html_writer::tag(
                'span',
                html_writer::tag('a', $filename, $link_param),
                array('class' => 'fp-filename-icon')
            );
            $result .= html_writer::tag('li', $filename);
        }
        $result .= '</ul>';

        return $result;
    }

}