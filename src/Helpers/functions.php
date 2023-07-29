<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use rhmdarif\Library\Log;

define('APACHE_MIME_TYPES_URL', 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');

if (!function_exists("format_rupiah")) {
    function format_rupiah($angka, $prefix = "Rp ", $decimal = 0)
    {
        $hasil_rupiah = $prefix . number_format($angka, $decimal, ',', '.');
        return $hasil_rupiah;
    }
}
if (!function_exists("date_modif")) {
    function date_modif($before, $commad, $format = '')
    {
        $new_time = strtotime($commad, strtotime($before));
        if ($format == '') return $new_time;

        return date($format, $new_time);
    }
}
if (!function_exists("get_number_jid")) {
    function get_number_jid($jid)
    {
        if (preg_match("/:/", $jid)) return explode(":", $jid)[0];
        if (preg_match("/@/", $jid)) return explode("@", $jid)[0];

        return $jid;
    }
}
if (!function_exists("phone_enam_dua")) {
    function phone_enam_dua($number)
    {
        $number = (int) get_number_jid($number);
        if (substr($number, 0, 2) != "62") $number = "62" . ((int) $number);
        return $number;
    }
}

if (!function_exists("send_to_webhook")) {
    function send_to_webhook($datas)
    {
        $validator = Validator::make($datas, [
            'webhook_url' => 'required|url',
            'datas' => 'required'
        ]);
        if ($validator->fails()) return false;

        Log::store("helpersFunction", __FUNCTION__, __LINE__, $datas['webhook_url'], $datas);

        $datas['datas']['timeStamp'] = time();
        Http::post($datas['webhook_url'], $datas['datas']);
        return true;
    }
}

if (!function_exists("rename_class")) {
    function rename_class($class)
    {
        return str_replace("\\", "/", $class);
    }
}


if (!function_exists("attachment_file_message")) {
    function attachment_file_message($type, $filepath)
    {
        $attachments = '';
        if ($type == "file") {
            $attachments .= '<div class="card border shadow-none mb-2">
                    <a href="javascript: void(0);" class="text-body">
                        <div class="p-2">
                            <div class="d-flex">
                                <div class="avatar-sm align-self-center me-2">
                                    <div class="avatar-title rounded bg-transparent text-primary font-size-18">
                                        <i class="uil uil-file-alt"></i>
                                    </div>
                                </div>

                                <div class="overflow-hidden me-auto">
                                    <h5 class="font-size-13 text-truncate mb-1">File</h5>
                                    <p class="text-muted text-truncate mb-0"><a href="' . $filepath . '" target="_blank">Download</a></p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>';
        } else if ($type == "document") {
            $attachments .= '<div class="card border shadow-none mb-2">
                    <a href="javascript: void(0);" class="text-body">
                        <div class="p-2">
                            <div class="d-flex">
                                <div class="avatar-sm align-self-center me-2">
                                    <div class="avatar-title rounded bg-transparent text-primary font-size-18">
                                        <i class="uil uil-file-alt"></i>
                                    </div>
                                </div>

                                <div class="overflow-hidden me-auto">
                                    <h5 class="font-size-13 text-truncate mb-1">Document</h5>
                                    <p class="text-muted text-truncate mb-0"><a href="' . $filepath . '" target="_blank">Download</a></p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>';
        } else if ($type == "sticker") {
            $attachments .= '<img class="img-fluid" src="' . $filepath . '">';
        } else if ($type == "image") {
            $attachments .= '<img class="img-fluid" src="' . $filepath . '">';
        }
        return $attachments;
    }
}


/*
if(!function_exists('web_config')) {
    function web_config($page, $key)
    {
        if(!Cache::has('web-config')) {
            $webconfigs = WebConfig::all();
            foreach ($webconfigs as $webconfig) {
                Cache::put('web-config.'.$webconfig->page.'.'.$webconfig->key, $webconfig->content, 10080);
            }
        }

        if(!Cache::has('web-config.'.$page.'.'.$key)) {
            $swebconfig = WebConfig::where('page', $page)->where('key', $key)->first();
            if($swebconfig != null) {
                Cache::put('web-config.'.$swebconfig->page.'.'.$swebconfig->key, $swebconfig->content, 10080);
            }
        }

        return Cache::get('web-config.'.$page.'.'.$key);
    }
}
*/

if (!function_exists('file_upload_exists')) {
    function file_upload_exists($file_path)
    {
        if (Storage::exists($file_path)) {
            Storage::delete($file_path);
        }

        return true;
    }
}


if (!function_exists('show_img')) {
    function show_img($file_path, $default='https://via.placeholder.com/300x300.png?text=empty')
    {
        $file_path = (is_string($file_path)) ? $file_path : "";

        if (filter_var($file_path, FILTER_VALIDATE_URL)) return $file_path;
        if (!empty($file_path) && Storage::exists($file_path)) return Storage::url($file_path);
        if (!empty($file_path) && file_exists(public_path($file_path))) return asset($file_path);
        if ($default != null && (empty($file_path) || (!Storage::exists($file_path) and !file_exists(public_path($file_path))))) return $default;

        return $file_path;
    }
}

if (!function_exists("removeKeywordOnString")) {
    function removeKeywordOnString($real, $remover)
    {
        return str_replace($remover, "", $real);
    }
}

if (!function_exists("generateUpToDateMimeArray")) {
    function generateUpToDateMimeArray($url)
    {
        $s = array();
        foreach (@explode("\n", @file_get_contents($url)) as $x)
            if (isset($x[0]) && $x[0] !== '#' && preg_match_all('#([^\s]+)#', $x, $out) && isset($out[1]) && ($c = count($out[1])) > 1)
                for ($i = 1; $i < $c; $i++)
                    $s[] = '&nbsp;&nbsp;&nbsp;\'' . $out[1][$i] . '\' => \'' . $out[1][0] . '\'';
        return @sort($s) ? '$mime_types = array(<br />' . implode($s, ',<br />') . '<br />);' : false;
    }
}

if (!function_exists("numberToAlphabet")) {
    function numberToAlphabet($number)
    {
        $number = intval($number);
        if ($number <= 0) {
            return '';
        }
        $alphabet = '';
        while ($number != 0) {
            $p = ($number - 1) % 26;
            $number = intval(($number - $p) / 26);
            $alphabet = chr(65 + $p) . $alphabet;
        }
        return $alphabet;
    }
}

if (!function_exists("alphabetToNumber")) {

    function alphabetToNumber($string)
    {
        $string = strtoupper($string);
        $length = strlen($string);
        $number = 0;
        $level = 1;
        while ($length >= $level) {
            $char = $string[$length - $level];
            $c = ord($char) - 64;
            $number += $c * (26 ** ($level - 1));
            $level++;
        }
        return $number;
    }
}
