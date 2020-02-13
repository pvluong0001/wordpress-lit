<?php

/**
 * Class Lit
 * https://www.youtube.com/api/timedtext?v=HzC2-GJu1Q8&asr_langs=de,en,es,fr,it,ja,ko,nl,pt,ru&caps=asr&xorp=true&hl=en&ip=0.0.0.0&ipbits=0&expire=1581285523&sparams=ip,ipbits,expire,v,asr_langs,caps,xorp&signature=E4FB77ED679C8C2852D88B9FC56CD457CD0334DD.7FC4F078CA1510C0E7CEC25CD747697ABE1EA61D&key=yt8&kind=asr&lang=en
 */

class Lit
{
    const GET_VIDEO_INFO_API = 'https://www.googleapis.com/youtube/v3/videos?id=VID&key=AIzaSyAqWWFOUlkEKqFUTpImsLgQhUo7BahLwAU&part=snippet';
    const GET_LANG_API = 'http://video.google.com/timedtext?type=list&v=VID';
    const GET_TRANSCRIPT_API = 'http://video.google.com/timedtext?lang=LANGVID&v=VID';
    const GET_LIST_VIDEO_BY_KEY_SEARCH = 'https://www.googleapis.com/youtube/v3/search?';
    const MY_API = 'http://ytapi.viralsoft.vn/?';

    public function __construct()
    {

        add_action('admin_menu', 'lit_add_menu');

        add_action('rest_api_init', function () {
            register_rest_route('lit/v1', '/add_post/', array(
                'methods' => 'POST',
                'callback' => 'add_post'
            ));

            register_rest_route('lit/v1', '/fetch_link/', array(
                'methods' => 'POST',
                'callback' => 'fetch_link'
            ));

            register_rest_route('lit/v1', '/import_multi/', array(
                'methods' => 'POST',
                'callback' => 'import_multi'
            ));

            register_rest_route('lit/v1', '/get_videos_name/', array(
                'methods' => 'POST',
                'callback' => 'get_videos_name'
            ));
        });

        add_action('admin_enqueue_scripts', 'my_admin_enqueue');

        function lit_add_menu()
        {
            add_menu_page('Goro Content Page', 'Goro Content', 'manage_options', 'lit-crawl', 'lit_example_menu');
        }

        function lit_example_menu()
        {
            GLOBAL $languages;

            set_query_var('languages', $languages);
            load_template(__DIR__ . '/views/page.php');
        }

        function my_admin_enqueue()
        {
            $page = $_GET['page'];

            if ($page == 'lit-crawl') {
                wp_register_style('litcss', plugin_dir_url(__FILE__) . 'lit.css');
                wp_register_style('bootstrap4-css',
                    'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css');
                wp_register_style('select2-css',
                    'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css');

                wp_register_script('bootstrap4-js',
                    'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js');
                wp_register_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js');
                wp_register_script('notifyjs', 'https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js');
                wp_register_script("lit_scripts", plugin_dir_url(__FILE__) . 'lit.js', array('jquery'));
                wp_localize_script('lit_scripts', 'litAjax', array(
                    'addpost' => get_site_url() . '/wp-json/lit/v1/add_post',
                    'fetchlink' => get_site_url() . '/wp-json/lit/v1/fetch_link',
                    'importmulti' => get_site_url() . '/wp-json/lit/v1/import_multi',
                    'get_videos_name' => get_site_url() . '/wp-json/lit/v1/get_videos_name',
                ));

                wp_enqueue_style('bootstrap4-css');
                wp_enqueue_style('litcss');
                wp_enqueue_style('select2-css');

                // enqueue jQuery library and the script you registered above
                wp_enqueue_script('jquery');
                wp_enqueue_script('lit_scripts');
                wp_enqueue_script('bootstrap4-js');
                wp_enqueue_script('select2-js');
                wp_enqueue_script('notifyjs');
            }
        }

        function get_videos_name() {
            $videoIds = $_POST['videoIds'];
            $lang = $_POST['lang'];

            return array_map(function($videoId) use ($lang) {
                try {
                    $content = @file_get_contents('http://youtube.com/get_video_info?video_id=' . $videoId);
                    parse_str($content, $ytarr);

                    $playerResponse = @json_decode($ytarr['player_response'], true);

                    $captionTracks = @$playerResponse['captions']['playerCaptionsTracklistRenderer']['captionTracks'];

                    if(is_array($captionTracks)) {
                        if(count($captionTracks) == 1) {
                            $content = getSubFromLink($captionTracks[0]['baseUrl']);
                        } else {
                            /** get video info */
//                            $videoInfo = _getVideoInfo($videoId);

                            /** get base lang */
//                            $lang = preg_replace('/-(.*)+/', '', $videoInfo->items[0]->snippet->defaultAudioLanguage);
                            $lang = $lang ? $lang : 'vi';

                            $baseUrl = '';
                            $defaultBaseUrl = $captionTracks[0]['baseUrl'];
                            foreach($captionTracks as $captionTrack) {
                                if(@$captionTrack['languageCode'] == 'en') {
                                    $defaultBaseUrl = $captionTrack['baseUrl'];
                                }

                                if(@$captionTrack['languageCode'] == $lang) {
                                    $baseUrl = $captionTrack['baseUrl'];
                                    break;
                                }
                            }

                            $captionUrl = $baseUrl ? $baseUrl : $defaultBaseUrl;

                            $content = getSubFromLink($captionUrl);
                        }
                    } else {
                        $content = null;
                    }

                    return [
                        'videoId' => $videoId,
                        'title' => @$playerResponse['videoDetails']['title'],
                        'length700' => strlen($content)
                    ];
                } catch(\Exception $exception) {
                    return [
                        'videoId'   => $videoId,
                        'title'     => null
                    ];
                }
            }, $videoIds);
        }

        function getSubFromLink($link) {
            $content = @file_get_contents($link);

            if(empty($content)) {
                return null;
            }

            $xml = new SimpleXMLElement($content);
            $temp = xml2array($xml);

            return implode($temp['text']);
        }

        function fetch_link()
        {
            try {
                $keywords = $_POST['keywords'];
                $number = $_POST['number'];
                $data = [];

                foreach ($keywords as $keyword) {
                    $tempData = @file_get_contents(self::GET_LIST_VIDEO_BY_KEY_SEARCH . http_build_query([
                            'q' => $keyword,
                            'key' => 'AIzaSyAqWWFOUlkEKqFUTpImsLgQhUo7BahLwAU',
                            'part' => 'snippet',
                            'maxResults' => $number
                        ]));
                    $data[] = [
                        'key' => $keyword,
                        'data' => json_decode($tempData)
                    ];
                }

                return $data;
            } catch (\Exception $exception) {

            }
        }

        function getSubFromMyApi($vid, $lang = 'en') {
            try {
                $lang = $lang ? $lang : 'en';
                $youtubeData = @file_get_contents(self::MY_API . http_build_query([
                    'vid' => $vid,
                    'lang' => $lang
                ]));

                if($youtubeData) {
                    return implode('<br>', array_map(function($item) {
                        return $item->text;
                    }, json_decode($youtubeData)));
                }

                return null;
            } catch(\Exception $exception) {
                return null;
            }
        }
        
        function import_multi() {
            try {
                $vidIds = $_POST['vidIds'];
                $tempData = $_POST;
                $videoHasNoSub = [];

                foreach($vidIds as $index => $vidId) {
                    $data = $tempData;
                    /** get video info */
                    $videoInfo = _getVideoInfo($vidId);

                    /** get content */
                    $lang = preg_replace('/-(.*)+/', '', $videoInfo->items[0]->snippet->defaultAudioLanguage);
                    $transcriptData = _getTranscript($vidId, $lang);

                    if(empty($transcriptData)){
                        $transcriptData = getSubFromMyApi($vidId, $lang);
                        if(empty($transcriptData)) {
                            $videoHasNoSub[] = $vidId;
                            unset($vidIds[$index]);
                            continue;
                        }
                    }

                    $data['post_title'] = $videoInfo->items[0]->snippet->title;
                    $data['post_status'] = 'draft';
                    $data['post_content'] = $transcriptData;

                    wp_insert_post($data);
                }

                return [
                    'status' => true,
                    'videoHasNoSub' => $videoHasNoSub
                ];
            } catch(\Exception $exception) {
                return [
                    'status' => false,
                    'message' => $exception->getMessage()
                ];
            }
        }

        function add_post()
        {
            try {
                $data = $_POST;
                $videoHasNoSub = [];
                /** get video info */
                $vidIds = $data['vids'];

                foreach($vidIds as $index => $vidId) {
                    $videoInfo = _getVideoInfo($vidId);

                    /** get content */
                    $lang = preg_replace('/-(.*)+/', '', $videoInfo->items[0]->snippet->defaultAudioLanguage);
                    $transcriptData = _getTranscript($vidId, $lang);

                    if(empty($transcriptData)){
                        $transcriptData = getSubFromMyApi($vidId, $lang);
                        if(empty($transcriptData)) {
                            $videoHasNoSub[] = $vidId;
                            unset($vidIds[$index]);
                            continue;
                        }
                    }

                    $data['post_title'] = $videoInfo->items[0]->snippet->title;
                    $data['post_status'] = 'draft';
                    $data['post_content'] = $transcriptData;

                    wp_insert_post($data);
                }

                return [
                    'status' => true,
                    'videoHasNoSub' => $videoHasNoSub
                ];
            } catch (\Exception $exception) {
                return [
                    'status' => false
                ];
            }
        }

        function xml2array($xmlObject, $out = array())
        {
            foreach ((array)$xmlObject as $index => $node) {
                $out[$index] = (is_object($node)) ? xml2array($node) : $node;
            }

            return $out;
        }

        function _getVideoInfo($videoId)
        {
            try {
                $youtubeData = @file_get_contents(str_replace('VID', $videoId, self::GET_VIDEO_INFO_API));

                return json_decode($youtubeData);
            } catch (\Exception $exception) {
                return null;
            }
        }

        function _getTranscript($videoId, $langVideo = 'en')
        {
            $api = 'http://video.google.com/timedtext?lang=' . $langVideo . '&v=' . $videoId;
            $transcriptData = @file_get_contents($api);
            
            if(empty($transcriptData)) {
                return null;
            }

            $xml = new SimpleXMLElement($transcriptData);
            $temp = xml2array($xml);

            return implode('<br>', $temp['text']);
        }
    }
}