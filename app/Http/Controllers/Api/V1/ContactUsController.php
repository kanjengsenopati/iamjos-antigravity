<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ContactUs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ContactUsRequest;

class ContactUsController extends Controller
{
    public function store(ContactUsRequest $request)
    {
        $data = $request->validated();
        if ($request->phone) {
            // check unique phone
            $phone = $data['phone'];
            if (substr($phone, 0, 1) == '0') {
                // Replace the leading "0" with "+62"
                $phone = '+62' . substr($phone, 1);
            } elseif (substr($phone, 0, 2) == '62' && substr($phone, 0, 3) != '+62') {
                // If it starts with "62" but not "+62", prepend "+"
                $phone = '+' . $phone;
            } elseif (substr($phone, 0, 1) != '+') {
                // If it does not start with "+", prepend "+62"
                $phone = '+62' . $phone;
            }
            $data['phone'] = $phone;
        }
        $contactUs = ContactUs::create($data);

        return $this->postSuccessResponse('Berhasil mengirim Pesan ke Admin', $contactUs);
    }
}
