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

    public function queue_user_sms($user_phone, $content_text)
    {
        if (DB::table('table_tbl_sms_queue')->insert(['phone_number' => $user_phone, 'text_content' => $content_text])) {
            return 1;
        }
        return 0;
    }

    public function reconcile_slag_click($slag)
    {
        DB::table('table_tbl_user_links')->where('slag', $slag)->update(['clicked' => 1]);
        return response()->json(['message' => 'Thank you for accepting to be part of this challenge.']);
    }

    public function create_slags_for_numbers()
    {
//        read out the contacts on the contacts table and loop through setting
        $contacts = DB::table('table_tbl_sms_contacts')->get();

        foreach ($contacts as $contact) {
            $slag = $this->generateRandomString();
            $message = URL::to('/') . "/" . $slag;
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
}
