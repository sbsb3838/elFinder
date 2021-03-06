<?php

class elFinderEditorOnlineConvert extends elFinderEditor
{
    protected $allowed = array('init', 'api');

    public function enabled()
    {
        return !defined('ELFINDER_DISABLE_ONLINE_CONVRT') || !ELFINDER_DISABLE_ONLINE_CONVRT;
    }

    public function init()
    {
        return array('api' => defined('ELFINDER_ONLINE_CONVRT_APIKEY') && ELFINDER_ONLINE_CONVRT_APIKEY && function_exists('curl_init'));
    }

    public function api()
    {
        // return array('apires' => array('message' => 'Currently disabled for developping...'));
        $endpoint = 'https://api2.online-convert.com/jobs';
        $category = $this->argValue('category');
        $convert = $this->argValue('convert');
        $options = $this->argValue('options');
        $source = $this->argValue('source');
        $filename = $this->argValue('filename');
        $jobid = $this->argValue('jobid');
        $string_method = '';
        $options = array();
        // Currently these converts are make error with API call. I don't know why.
        $nonApi = array('android','blackberry','dpg','ipad','iphone','ipod','nintendo-3ds','nintendo-ds','ps3','psp','wii','xbox');
        if (in_array($convert, $nonApi)) {
            return array('apires' => array());
        }
        $ch = null;
        if ($convert && $source) {
            $request = array(
                'input' => array(array(
                    'type' => 'remote',
                    'source' => $source
                )),
                'conversion' => array(array(
                    'target' => $convert
                ))
            );

            if ($filename !== '') {
                $request['input'][0]['filename'] = $filename;
            }

            if ($category) {
            	$request['conversion'][0]['category'] = $category;
            }

            if ($options && $options !== 'null') {
                $options = json_decode($options, true);
            }
            if (!is_array($options)) {
                $options = array();
            }
            if ($options) {
            	$request['conversion'][0]['options'] = $options;
            }

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Oc-Api-Key: ' . ELFINDER_ONLINE_CONVRT_APIKEY,
                'Content-Type: application/json',
                'cache-control: no-cache'
            ));
        } else if ($jobid) {
            $ch = curl_init($endpoint . '/' . $jobid);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Oc-Api-Key: ' . ELFINDER_ONLINE_CONVRT_APIKEY,
                'cache-control: no-cache'
            ));
        }

        if ($ch) {
            $response = curl_exec($ch);
            $info = curl_getinfo($ch);
            $error =  curl_error($ch);
            curl_close($ch);

            if (! empty($error)) {
                $res = array('error' => $error);
            } else {
                $res = array('apires' => json_decode($response, true));
            }

            return $res;
        } else {
            return array('error' => array('errCmdParams', 'editor.OnlineConvert.api'));
        }
    }
}
