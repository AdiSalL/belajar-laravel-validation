<?php

namespace App\Http\Controllers;

use Dotenv\Exception\ValidationException;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Response as FacadesResponse;

class FormController extends Controller
{
    //
    public function login(Request $request): HttpResponse {
        try {
            $data = $request->validate([
                "username" => "required",
                "password" => "required",
            ]);
            return response("OK", HttpResponse::HTTP_OK);
        }catch (ValidationException $exception) {
            return response()->json(['error' => $exception->getMessage()], HttpResponse::HTTP_BAD_REQUEST);
        }
    }
}
