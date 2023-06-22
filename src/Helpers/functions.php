<?php

use App\Models\WebConfig;
use App\Helpers\LogHelpers;
use App\Models\BotScenario;
use App\Models\BotCampaignScenario;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Lib\WhatsappController;

if (!function_exists("current_agent")) {
    function current_agent()
    {
        if(auth()->check()) return auth()->user();

        $token = PersonalAccessToken::findToken(Session::get("agent.token"));
        if ($token == null) return null;
        $user = $token->tokenable;
        return $user;
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
        if (substr($number, 0, 1) == "8") $number = "62" . ((int) $number);
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

        LogHelpers::store("helpersFunction", __FUNCTION__, __LINE__, $datas['webhook_url'], $datas);

        $datas['datas']['timeStamp'] = time();
        Http::post($datas['webhook_url'], $datas['datas']);
        return true;
    }
}

if (!function_exists("fetch_scenario_lists")) {
    function fetch_scenario_lists(BotScenario $bot_scenario)
    {
        $result = '
        <div class="card border shadow-none">
            <div class="card-body p-4">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="font-size-15 mb-1 text-truncate text-dark">
                         ' . ($bot_scenario->message ?? '-') . ' (Id : #' . $bot_scenario->id . ')
                        </h5>
                        <p class="text-muted text-truncate mb-0">
                            ' . (($bot_scenario->bot_scenario_id != null) ? '(Parent Id : #' . $bot_scenario->bot_scenario_id . ')' : '') . '
                        </p>
                    </div>
                    <div class="flex-shrink-0 dropdown">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal icon-xs"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:scenarioEdit(' . $bot_scenario->id . ')">Edit</a>
                            <a class="dropdown-item" href="javascript:scenarioDelete(' . $bot_scenario->id . ')">Delete</a>
                            <hr class="dropdown-divider">
                            <a class="dropdown-item" href="javascript:scenarioCreate(' . $bot_scenario->bot_id . ', ' . $bot_scenario->id . ')">Create Child</a>
                            <hr class="dropdown-divider">
                            <a class="dropdown-item" href="javascript:replyCreate(' . $bot_scenario->id . ')">Create Response</a>
                        </div>
                    </div><!-- end dropdown -->
                </div>
            </div>
            <!-- end card body -->
            <div class="btn-group btn-icon" role="group">';

        // Tombol child
        if ($bot_scenario->bot_scenarios->count() == 0) {
            $result .= '<button type="button" class="btn btn-outline-light text-dark" data-bs-toggle="collapse">Childs</button>';
        } else {
            $result .= '<button type="button" class="btn btn-outline-light text-dark" data-bs-toggle="collapse"
                data-bs-target="#childs-' . $bot_scenario->id . '"
                aria-expanded="false" aria-controls="collapseExample">Childs ( ' . $bot_scenario->bot_scenarios->count() . ' )</button>';
        }

        // Tombol Response
        $result .= '<button type="button" class="btn btn-outline-light text-dark" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Message" onclick="showResponse(' . $bot_scenario->id . ')">Response</button>';

        $result .= '
            </div>
        </div>';

        if ($bot_scenario->bot_scenarios->count() > 0) {
            $result .= '<div class="collapse p-0" id="childs-' . $bot_scenario->id . '">';
        }

        foreach ($bot_scenario->bot_scenarios as $bot_scenario_child) {
            $result .= fetch_scenario_lists($bot_scenario_child);
        }

        if ($bot_scenario->bot_scenarios->count() > 0) {
            $result .= '</div>';
        }


        return $result;
    }
}

if (!function_exists("fetch_campaign_scenario_lists")) {
    function fetch_campaign_scenario_lists(BotCampaignScenario $bot_scenario)
    {
        $result = '
        <div class="card border shadow-none">
            <div class="card-body p-4">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="font-size-15 mb-1 text-truncate text-dark">
                         ' . ($bot_scenario->message ?? '-') . ' (Id : #' . $bot_scenario->id . ')
                        </h5>
                        <p class="text-muted text-truncate mb-0">
                            ' . (($bot_scenario->bot_scenario_id != null) ? '(Parent Id : #' . $bot_scenario->bot_scenario_id . ')' : '') . '
                        </p>
                    </div>
                    <div class="flex-shrink-0 dropdown">
                        <a class="text-body dropdown-toggle font-size-16" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-more-horizontal icon-xs"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="javascript:scenarioEdit(' . $bot_scenario->id . ')">Edit</a>
                            <a class="dropdown-item" href="javascript:scenarioDelete(' . $bot_scenario->id . ')">Delete</a>
                            <hr class="dropdown-divider">
                            <a class="dropdown-item" href="javascript:scenarioCreate(' . $bot_scenario->bot_campaign_id . ', ' . $bot_scenario->id . ')">Create Child</a>
                        </div>
                    </div><!-- end dropdown -->
                </div>
            </div>
            <!-- end card body -->
            <div class="btn-group btn-icon" role="group">';

        // Tombol child
        if ($bot_scenario->bot_scenarios->count() == 0) {
            $result .= '<button type="button" class="btn btn-outline-light text-dark" data-bs-toggle="collapse">Childs</button>';
        } else {
            $result .= '<button type="button" class="btn btn-outline-light text-dark" data-bs-toggle="collapse"
                data-bs-target="#childs-' . $bot_scenario->id . '"
                aria-expanded="false" aria-controls="collapseExample">Childs ( ' . $bot_scenario->bot_scenarios->count() . ' )</button>';
        }



        $result .= '
            </div>
        </div>';

        if ($bot_scenario->bot_scenarios->count() > 0) {
            $result .= '<div class="collapse p-0" id="childs-' . $bot_scenario->id . '">';
        }

        foreach ($bot_scenario->bot_scenarios as $bot_scenario_child) {
            $result .= fetch_campaign_scenario_lists($bot_scenario_child);
        }

        if ($bot_scenario->bot_scenarios->count() > 0) {
            $result .= '</div>';
        }


        return $result;
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
                                    <p class="text-muted text-truncate mb-0"><a href="'.$filepath.'" target="_blank">Download</a></p>
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
                                    <p class="text-muted text-truncate mb-0"><a href="'.$filepath.'" target="_blank">Download</a></p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>';
        } else if ($type == "sticker") {
            $attachments .= '<img class="img-fluid" src="'.$filepath.'">';
        } else if ($type == "image") {
            $attachments .= '<img class="img-fluid" src="'.$filepath.'">';
        }
        return $attachments;
    }
}



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

if(!function_exists('file_upload_exists')) {
    function file_upload_exists($file_path)
    {
        if(Storage::exists($file_path)) {
            Storage::delete($file_path);
        }

        return true;
    }
}


if(!function_exists('show_img')) {
    function show_img($file_path="", $type='lebar')
    {

        $file_path = (is_string($file_path))? $file_path : "";

        if(!empty($file_path) && Storage::exists($file_path)) {
            return Storage::url($file_path);
        }
        if(!empty($file_path) && file_exists(public_path($file_path))) {
            return asset($file_path);
        }

        if(empty($file_path) || (!Storage::exists($file_path) AND !file_exists(public_path($file_path)))) {
            if ($type == "lebar") {
                $file_path = web_config("config", "img.default-lebar") ?? "/assets/img/default/lebar.png";
            } else if ($type == "persegi") {
                $file_path = web_config("config", "img.default-persegi") ?? "/assets/img/default/persegi.jpg";
            } else if ($type == "footer") {
                $file_path = web_config("config", "img.default-footer") ?? "/assets/img/default/footer.png";
            }
        }


        return $file_path;
    }
}
