<?php

/**
 * class AutoFB - Support functions for String
 *
 * @package Lib
 * @created 2018-11-15
 * @version 1.0
 * @author AnhMH
 * @copyright Oceanize INC
 */

namespace Lib;

class YtbDownloader {

    public static $link_pattern = "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed)\/))([^\?&\"'>]+)/";
    public static $video_url = '';
    public static $video_title = '';
    public static $_url_search = 'https://www.googleapis.com/youtube/v3/search';
    
    public static function downloader($url) {
        if (!self::validateUrl($url)) {
            echo 'Please provide valid YouTube URL.';
            return false;
        }
        self::$video_url = $url;
        
        if (self::hasVideo()) {
            $videoDownloadLink = self::getVideoDownloadLink();
            
            $videoTitle = $videoDownloadLink[0]['title'];
            $videoQuality = $videoDownloadLink[0]['quality'];
            $videoFormat = $videoDownloadLink[0]['format'];
            $videoFileName = strtolower(str_replace(' ', '_', $videoTitle)) . '.' . $videoFormat;
            $downloadURL = $videoDownloadLink[0]['url'];
            
            return $downloadURL;
            // 
            $fileName = preg_replace('/[^A-Za-z0-9.\_\-]/', '', basename($videoFileName));
            if (!empty($downloadURL)) {
                // Define headers
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$fileName");
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: binary");

                // Read the file
                readfile($downloadURL);
            }
        } else {
            echo 'The video is not found, please check YouTube URL.';
            return false;
        }
    }
    
    /*
     * Validate url
     * return boolean
     */
    public static function validateUrl ($url) {
        if (empty($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }
        
        if (preg_match(self::$link_pattern, $url)){
            return true;
        }
        
        return false;
    }
    
    /*
     * Validate the given video url
     * return bool
     */
    public static function hasVideo(){
        $valid = true;
        parse_str(self::getVideoInfo(), $data);
        if($data["status"] == "fail"){
            $valid = false;
        } 
        return $valid;
    }
    
    /*
     * Get the video information
     * return string
     */
    public static function getVideoInfo() {
        return file_get_contents("https://www.youtube.com/get_video_info?video_id=".self::extractVideoId(self::$video_url)."&cpn=CouQulsSRICzWn5E&eurl&el=adunit");
    }
    
    /*
     * Get video Id
     * @param string
     * return string
     */
    public static function extractVideoId($video_url){
        //parse the url
        $parsed_url = parse_url($video_url);
        
        if ($parsed_url["path"] == "youtube.com/watch"){
            self::$video_url = "https://www.".$video_url;
        } elseif ($parsed_url["path"] == "www.youtube.com/watch"){
            self::$video_url = "https://".$video_url;
        }
        
        if (isset($parsed_url["query"])){
            $query_string = $parsed_url["query"];
            //parse the string separated by '&' to array
            parse_str($query_string, $query_arr);
            if(isset($query_arr["v"])){
                return $query_arr["v"];
            }
        }   
    }
    
    /*
     * Get the video download link
     * return array
     */
    public static function getVideoDownloadLink(){
        //parse the string separated by '&' to array
        parse_str(self::getVideoInfo(), $data);
         
        //set video title
        self::$video_title = $data["title"];
         
        //Get the youtube root link that contains video information
        $stream_map_arr = self::getStreamArray();
        $final_stream_map_arr = array();
         
        //Create array containing the detail of video 
        foreach($stream_map_arr as $stream){
            parse_str($stream, $stream_data);
            $stream_data["title"] = self::$video_title;
            $stream_data["mime"] = $stream_data["type"];
            $mime_type = explode(";", $stream_data["mime"]);
            $stream_data["mime"] = $mime_type[0];
            $start = stripos($mime_type[0], "/");
            $format = ltrim(substr($mime_type[0], $start), "/");
            $stream_data["format"] = $format;
            unset($stream_data["type"]);
            $final_stream_map_arr [] = $stream_data;         
        }
        return $final_stream_map_arr;
    }
    
    /*
     * Get the youtube root data that contains the video information
     * return array
     */
    public static function getStreamArray(){
        parse_str(self::getVideoInfo(), $data);
        $stream_link = $data["url_encoded_fmt_stream_map"];
        return explode(",", $stream_link); 
    }
    
    /*
     * Get list video
     * return array
     */
    public static function ytbSearch($key, $channelId = '', $keyword = '', $maxResult = '50'){
        $param = array(
            'key' => $key,
            'order' => 'date',
            'part' => 'snippet',
            'type' => 'video',
            'maxResults' => $maxResult
        );
        if (!empty($channelId)) {
            $param['channelId'] = $channelId;
        }
        if (!empty($keyword)) {
            $param['q'] = $keyword;
        }
        $url = self::$_url_search;
        $url .= "?".http_build_query($param);
        
        $data = json_decode(\Lib\AutoFB::call($url), true);
        
        return $data;
    }
}
