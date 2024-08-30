<?php

namespace Tests\Feature;

use App\Rules\RegistrationRule;
use App\Rules\Uppercase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Password as RulesPassword;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as ValidationValidator;
use Tests\TestCase;

use function PHPUnit\Framework\assertNotNull;

class ValidatorTest extends TestCase
{
    public function testValidator() {
        $data = [
            "username" => "admin",
            "password" => "12345"
        ];

        $rules = [
            "username" => "required",
            "password" => "required"
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);
        self::assertTrue($validator->passes());
        self::assertFalse($validator->fails());
        
    }

    public function testValidatorInvalid() {
        $data = [
            "username" => "",
            "password" => "12345"
        ];

        $rules = [
            "username" => "required",
            "password" => "required"
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);
        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());
        
        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorException() {
        $data = [
            "username" => "",
            "password" => "12345"
        ];

        $rules = [
            "username" => "required",
            "password" => "required"
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        try {
            $validator->validate();
            self::fail("Validation Exception Not Thrown");
        }catch(ValidationException $exception) {
            self::assertNotNull($exception->validator);
            $message = $exception->validator->errors();
            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
        
    }

    public function testValidatorMultipleRules() {
        App::setLocale("id"); 
        $data = [
            "username" => "adi",
            "password" => "dick"
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"]
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);
        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());
        
        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorValidate() {
        $data = [
            "username" => "admin@pzn.com",
            "password" => "admin123",
            "is_admin" => true
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => "required|min:6|max:20",
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        try {
            $valid  = $validator->validate();
        
            Log::info($valid);
            
        }catch(ValidationException $exception) {
            self::assertNotNull($exception->validator);
            $message = $exception->validator->errors();
            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
    }

    public function testValidatorInlineMessage() {

        $data = [
            "username" => "adi",
            "password" => "dick"
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"]
        ];

        $messages = [
            "required" => ":attribute harus diisi",
            "min" => ":attribute harus minimal :min karakter",
            "max" => ":attribute harus maximal :max karakter",
            "email" => ":attribute harus format email",
            
        ];

        $validator = Validator::make($data, $rules, $messages);
        self::assertNotNull($validator);
        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());
        
        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorAdditionalValidation() {
        
        $data = [
            "username" => "adi@mail.com",
            "password" => "adi@mail.com"
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"]
        ];

        $validator = Validator::make($data, $rules);
        $validator->after(function (ValidationValidator $validator) {
            $data = $validator->getData();
            if($data["username"] == $data["password"]) {
                $validator->errors()->add("password", "Password tidak boleh sama dengan username");
            }
        });
        self::assertNotNull($validator);
        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());
        
        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    
    public function testValidatorCustomRule() {
        
        $data = [
            "username" => "ida@mail.com",
            "password" => "adi@mail.com"
        ];

        $rules = [
            "username" => ["required", "email" ," max:100", new Uppercase()],
            "password" => ["required", "min:6", "max:20", new RegistrationRule()]
        ];

        $validator = Validator::make($data, $rules);

        self::assertNotNull($validator);
        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());
        
        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorCustomFunctionRule() {
        
        $data = [
            "username" => "ida@mail.com",
            "password" => "adi@mail.com"
        ];

        $rules = [
            "username" => ["required", "email" ," max:100", function(string $attribute, string $value, \Closure $fail) {
                if($value != strtoupper($value)) {
                    $fail("validation.custom.uppercase");
                }
            }],
            "password" => ["required", "min:6", "max:20", new RegistrationRule()]
        ];

        $validator = Validator::make($data, $rules);

        self::assertNotNull($validator);
        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());
        
        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorRuleClasses() {
        
        $data = [
            "username" => "Ida",
            "password" => "password123'"
        ];

        $rules = [
            "username" => ["required", new In("Eko", "Ida", "Budi")],
            "password" => ["required", RulesPassword::min(6)->letters()->numbers()->symbols()],
            
        ];

        $validator = Validator::make($data, $rules);

        self::assertNotNull($validator);
        self::assertTrue($validator->passes());
        self::assertFalse($validator->fails());
        
        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorNestedArray() {
        
        $data = [
            "name" => [
                "first" => "Adi", 
                "last" => "Salafudin"
            ],
            "address" => [
                "no" => "01",
                "street" => "Jl. Kebon Jeruk",
                "city" => "Jakarta"
            ]
        ];

        $rules = [
            "name.first" => ['required', "max:100"],
            "name.last" => ["max:100"],
            "address.no" => ["max:100"],
            "address.street" => ["required", "max:100"],
            "address.city" => ["max:100"],
        ];

        $validator = Validator::make($data, $rules);

        self::assertNotNull($validator);
        self::assertTrue($validator->passes());
        self::assertFalse($validator->fails());
        
        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testNestedArrayIndex() {
        
        $data = [
            "name" => [
                "first" => "Adi", 
                "last" => "Salafudin"
            ],
            "address" => [
                [
                    "no" => "01",
                    "street" => "Jl. Kebon Jeruk",
                    "city" => "Jakarta"
                ],
                [
                    "no" => "10",
                    "street" => "Jl. Kebon Manggis",
                    "city" => "Bekasi"
                ]
            ]
        ];

        $rules = [
            "name.first" => ['required', "max:100"],
            "name.last" => ["max:100"],
            "address.*.no" => ["max:100"],
            "address.*.street" => ["required", "max:100"],
            "address.*.city" => ["max:100"],
        ];

        $validator = Validator::make($data, $rules);

        self::assertNotNull($validator);
        self::assertTrue($validator->passes());
        self::assertFalse($validator->fails());
        
        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }
}
