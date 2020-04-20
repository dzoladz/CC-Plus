<?php

namespace App\Http\Controllers;

use App\SushiSetting;
use App\Institution;
use App\Provider;
use App\Sushi;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
//Enables us to output flash messaging
use Session;

class SushiSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
       // Validate form inputs
        $this->validate($request, ['inst_id' => 'required', 'prov_id' => 'required']);

        // User must be an admin or member-of inst to get the settings
        if (!(auth()->user()->hasRole("Admin") || auth()->user()->inst_id==$request->inst_id)) {
            return response()->json(array('error' => 'Invalid request'));
        }

       // Get sushi URL from provider record
        $server_url = Provider::where('id', '=', $request->prov_id)->value('server_url_r5');

       // Get the settings
        $_where = ['inst_id' => $request->inst_id,
                 'prov_id' => $request->prov_id];
        $data = SushiSetting::where($_where)->first();
        $settings = (is_null($data)) ? array('count' => 0) : $data->toArray();

       // Return settings and url as json
        $return = array('settings' => $settings, 'url' => $server_url);
        return response()->json($return);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasAnyRole(['Admin','Staff','Manager'])) {
            return response()->json(['result' => false, 'msg' => 'Update failed (403) - Forbidden']);
        }

        $input = $request->all();
        if (!auth()->user()->hasAnyRole(['Admin','Staff']) &&
            $input['inst_id'] != auth()->user()->inst_id) {
            return response()->json(['result' => false, 'msg' => 'You can only assign settings for your institution']);
        }
        $setting = SushiSetting::create($input);
        return response()->json(['result' => true, 'msg' => 'Settings successfully created']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request)
    {
       // Validate form inputs
        $this->validate($request, ['inst_id' => 'required', 'prov_id' => 'required']);
        $input = $request->all();

       // Ensure user is allowed to change the settings
        $institution = Institution::findOrFail($request->inst_id);
        if (!$institution->canManage()) {
            return response()->json(array('error' => 'Invalid request'));
        }

       // Update or create the settings
        $setting = SushiSetting::updateOrCreate(
            ['inst_id' => $request->inst_id, 'prov_id' => $request->prov_id],
            $input
        );
    }

    /**
     * Test the Sushi settings for a given provider-institution.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function test(Request $request)
    {
       // Validate form inputs
        $this->validate($request, ['prov_id' => 'required']);
        $provider = Provider::findOrFail($request->prov_id);

        // Begin setting up the URI by cleaning/standardizing the server_url_r5 string in the setting
         $_url = rtrim($provider->server_url_r5);    // remove trailing whitespace
         $_url = preg_replace('/\/?reports\/?/i', '', $_url); // take off any methods with any bounding slashes
         $_url = preg_replace('/\/?status\/?/i', '', $_url);  //   "   "   "     "      "   "     "        "
         $_url = preg_replace('/\/?members\/?/i', '', $_url); //   "   "   "     "      "   "     "        "
         $_uri = rtrim($_url, '/');                           // remove any remaining trailing slashes

         // Construct and execute the test request
         $_uri .= '/status/';
         $uri_auth = "?customer_id=" . urlencode($request->customer_id);
         if (!is_null($request->requestor_id)) {
             $uri_auth .= "&requestor_id=" . urlencode($request->requestor_id);
         }
         if (!is_null($request->API_key)) {
             $uri_auth .= "&api_key=" . urlencode($request->API_key);
         }
         $request_uri = $_uri . $uri_auth;

        // Make the request and convert result into JSON
         $rows = array();
         $client = new Client();   //GuzzleHttp\Client
         try {
              $response = $client->get($request_uri);
              $rows[] = "JSON Response:";
              $rows[] = json_decode($response->getBody(), JSON_PRETTY_PRINT);
              $result = 'Service status successfully received';
         } catch (\Exception $e) {
             $result = 'Request for service status failed!';
             if ($e->hasResponse()) {
                 $response = $e->getResponse();
                 $rows[] = "Error Returned: (" . $response->getStatusCode() . ") ";
                 $rows[] = $response->getReasonPhrase();
             } else {
                 $rows[] = "No response from provider.";
             }
          }

       // return ... something
        $return = array('rows' => $rows, 'result' => $result);
        return response()->json($return);

       // // Validate form inputs
       //  $this->validate($request, ['inst_id' => 'required', 'prov_id' => 'required']);
       //  $input = $request->all();
       //
       // // Get the settings
       //  $setting = SushiSetting::where([
       //                              ['inst_id', '=', $request->inst_id],
       //                              ['prov_id', '=', $request->prov_id]
       //                          ])->first();
       //
       // // Create a new Sushi object and request_uri (dates don't matter)
       //  $sushi = new Sushi("","");
       //  $request_uri = $sushi->buildUri($setting, 'status');
       //
       // // Make the request and convert into JSON
       //  $rows = array();
       //  $client = new Client();   //GuzzleHttp\Client
       //  try {
       //       $response = $client->get($request_uri);
       //       $rows[] = "JSON Response:";
       //       $rows[] = json_decode($response->getBody(), JSON_PRETTY_PRINT);
       //       // foreach ($json_output as $key => $value) {
       //       //     $rows[] = $key . " : " . $value;
       //       // }
       //       $result = 'Service status successfully received';
       //  } catch (\Exception $e) {
       //      $result = 'Request for service status failed!';
       //      if ($e->hasResponse()) {
       //          $response = $e->getResponse();
       //          $rows[] = "Error Returned: (" . $response->getStatusCode() . ") ";
       //          $rows[] = $response->getReasonPhrase();
       //      } else {
       //          $rows[] = "No response from provider.";
       //      }
       //  }
       //
       // // return ... something
       //  $return = array('rows' => $rows, 'result' => $result);
       //  return response()->json($return);
    }
}
