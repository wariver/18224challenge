<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class WorkController extends Controller
{
    //
    public function insert_database_records()
    {

    }

    public function working()
    {
        echo "happy";
    }

    public function queue_user_sms($user_phone, $content_text)
    {
        if (DB::table('table_tbl_sms_queue')->insert(['phone_number' => $user_phone, 'text_content' => $content_text])) {
            return 1;
        }
        return 0;
    }

    public function reconcile_slag_click($slag)
    {
        DB::table('table_tbl_user_links')->where('slag', $slag)->update(['clicked' => DB::raw('clicked+1')]);
        return view('welcome');
    }

    public function create_slags_for_numbers()
    {
//        read out the contacts on the contacts table and loop through setting
        $contacts = DB::table('table_tbl_sms_contacts')->get();

        foreach ($contacts as $contact) {
            $slag = $this->generateRandomString();
            $message = URL::to('/api/participate') . "/" . $slag;
            DB::table('table_tbl_user_links')->insert(['phone' => $contact->phone_number, 'slag' => $slag]);
            $this->queue_user_sms($contact->phone_number, $message);
        }
        return "DONE";
    }

    private function generateRandomString($length = 5)
    {
        $characters = 'abcdef1234567890';
        $charactersLen = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            try {
                $randomString .= $characters[random_int(0, $charactersLen - 1)];
            } catch (\Exception $e) {
            }
        }
        return $randomString;
    }

    public function send_to_next()
    {
        $sms_q = DB::table('table_tbl_sms_queue')->first();
        $id = $sms_q->id;
        $msg = $sms_q->text_content;
        $phone = $sms_q->phone_number;

        $firebase_token = DB::table('tbl_fb_instance')->where('id', 1)->first();
        if (!$firebase_token) {
            return response()->json(['status' => 500, 'error' => 'There was no android device registered.']);
        }

        $fields = [
            'to' => (string)$firebase_token->fb_instance_id,
            'data' => [
                'title' => (string)$phone,
                'body' => (string)$msg
            ]
        ];
        $data = json_encode($fields);
        $header = [
            'Authorization:key=AAAA0aRdlwE:APA91bFC2rwdQEaeav_nybFTY5C7NyQDQqFvj7tpgZeu2AI4_RgV-2J_UmwKfAra9ZuOB0UEDT1jmd4d335L07xbbivAFmhB2XWfLZRrrLKj-YY0G91KdMO1OypZEZt9tbpKR4XNaLvA',
            'Content-Type:application/json'
        ];
        $url = 'https://fcm.googleapis.com/fcm/send';
//        $client->post($url, ['body' => json_encode($fields)]);
        $cURLConnection = curl_init();

        curl_setopt($cURLConnection, CURLOPT_POST, true);
        curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, $header);
        curl_setopt($cURLConnection, CURLOPT_URL, $url);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $data);
//        dd($url, $data);
        $result = curl_exec($cURLConnection);
        var_dump($result);
        DB::table('table_tbl_sms_queue')->where('id', $id)->delete();
    }
}
