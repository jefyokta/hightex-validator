<?php

use Jefyokta\HightexValidator\Exception\PluginException;
use Jefyokta\HightexValidator\Plugin\UnreferedPlugin;
use Jefyokta\HightexValidator\Validator;
use Tests\Helper\NodePluginExample;
use Tests\Helper\PunctuationPluginExample;

test('sanity check', function () {
    expect(true)->toBeTrue();
});



// test("unreferenced image detected", function () {
//     $result = Validator::make([
//         ["type" => "image"]
//     ])->check();

//     expect($result->unfigImage)->toBe(1);
// });

// test("image with figcaption is valid", function () {
//     $result = Validator::make([
//         ["type" => "image"],
//         ["type" => "figcaption"]
//     ])->check();

//     expect($result->unfigImage)->toBe(0);
// });




test("punctuation: space before comma and repeated punctuation", function () {
    $text = "jepi ,,";

    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => $text]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toHaveCount(1);

    $err = $result->punctuationErrors[0];

    expect($err->text)->toBe($text);
    expect($err->errors[0])->toBe("Terdapat spasi sebelum tanda baca.");
    expect($err->errors[1])->toBe("Tanda baca ditulis berulang.");
});

test("punctuation: merged text nodes validated as one sentence", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "jepi"],
                ["type" => "text", "text" => " ,"]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toHaveCount(1);

    $err = $result->punctuationErrors[0];

    expect($err->text)->toBe("jepi ,");
    expect($err->errors[0])->toBe("Terdapat spasi sebelum tanda baca.");
});

test("punctuation: inline node does not break validation", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "menurut"],
                ["type" => "cite"],
                ["type" => "text", "text" => " , sistem"]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toHaveCount(1);

    $err = $result->punctuationErrors[0];

    expect($err->text)->toBe("menurut{cite} , sistem");
    expect($err->errors[0])->toBe("Terdapat spasi sebelum tanda baca.");
});


test("valid sentence produces no punctuation errors", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "Ini adalah kalimat yang benar."]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toBeEmpty();
});



test("multiple paragraphs produce multiple punctuation errors", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "salah ,"]
            ]
        ],
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "juga salah!!"]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toHaveCount(2);
});

test("nested content is validated recursively", function () {
    $result = Validator::make([
        [
            "type" => "doc",
            "content" => [
                [
                    "type" => "paragraph",
                    "content" => [
                        ["type" => "text", "text" => "nested , error"]
                    ]
                ]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toHaveCount(1);
});



test("isOk returns false when validation errors exist", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "salah ,"]
            ]
        ]
    ])->check();

    expect($result->isOk())->toBeFalse();
});

test("isOk returns true when no validation errors exist", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "Kalimat ini benar."]
            ]
        ]
    ])->check();

    expect($result->isOk())->toBeTrue();
});


test("empty paragraph does not produce punctuation error", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => []
        ]
    ])->check();

    expect($result->punctuationErrors)->toBeEmpty();
});

test("whitespace only text does not produce punctuation error", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "   "]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toBeEmpty();
});



test("multiple inline nodes between text are handled correctly", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "menurut "],
                ["type" => "cite"],
                ["type" => "text", "text" => " , sistem"]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toHaveCount(1);

    $err = $result->punctuationErrors[0];
    expect($err->text)->toBe("menurut {cite} , sistem");
    expect($err->errors)->toContain("Terdapat spasi sebelum tanda baca.");
});

test("inline node at beginning of paragraph does not break text", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "cite"],
                ["type" => "text", "text" => " ,salah"]
            ]
        ]
    ])->check();

    $err = $result->punctuationErrors[0];
    expect($err->text)->toBe("{cite} ,salah");
});



test("one paragraph can contain multiple punctuation error types", function () {
    $text = "ini ,,, salah";

    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => $text]
            ]
        ]
    ])->check();

    $err = $result->punctuationErrors[0];

    expect($err->errors)->toContain("Terdapat spasi sebelum tanda baca.");
    expect($err->errors)->toContain("Tanda baca ditulis berulang.");
});







test("comma without space before is valid", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "kata,kata"]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toBeEmpty();
});
test("three dots is excluded", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "..."]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toBeEmpty();
});

test("single punctuation is valid", function () {
    $result = Validator::make([
        [
            "type" => "paragraph",
            "content" => [
                ["type" => "text", "text" => "Benar."]
            ]
        ]
    ])->check();

    expect($result->punctuationErrors)->toBeEmpty();
});



test("add plugin ", function () {

    $result = ($v = Validator::make(
        nodes: [
            [
                "type" => "paragraph",
                "content" => [
                    [
                        "type" => "text",
                        "text" => str_repeat("Test ", 202)
                    ]
                ]
            ]
        ],
        plugins: [
            PunctuationPluginExample::class
        ]
    ))->check();


    expect($v->getPlugins())->toHaveCount(2);
});
test("add plugin : node ", function () {

    $v = Validator::make(
        nodes: [
            [
                "type" => "paragraph",
                "content" => [
                    [
                        "type" => "text",
                        "text" => str_repeat("Test ", 202)
                    ]
                ]
            ]
        ],
        plugins: [
            NodePluginExample::class
        ]
    );


    expect($v->getPlugins())->toHaveCount(2);
});


test("punctuation plugin test ", function () {

    $result = (Validator::make(
        nodes: [
            [
                "type" => "paragraph",
                "content" => [
                    [
                        "type" => "text",
                        "text" => 'sdada'
                    ]
                ]
            ]
        ],
        plugins: [
            PunctuationPluginExample::class
        ]
    ))->check();


    expect($result->punctuationErrors)->toHaveCount(1);
});

test("node plugin test", function () {
    $err = Validator::make(
        plugins: [
            NodePluginExample::class,

        ],
        nodes: [["type" => "test"]]
    )->check()
        ->nodeErrors;
    expect($err)->toHaveCount(1);
});

test("cannot registering non-plugin class", function () {
    expect(
        fn() =>
        Validator::make(plugins: [
            Validator::class
        ])
    )->toThrow(PluginException::class);
});


test("counting unrefered table & image ", function () {

    $v = Validator::make(
        nodes: [
            [
                "type" => "paragraph",
                "content" => [
                    [
                        "type" => "text",
                        "text" => str_repeat("Test ", 202)
                    ],
                    [
                        "type" => "refComponent",
                        "attrs" => [
                            "ref" => "imageFigure",
                            "link" => "fig:1"
                        ]
                    ]
                ],

            ],
            [
                "type" => "imageFigure",
                "attrs" => ["id" => "fig:1"],
                "content" => [
                    ["type" => "image"],
                    ["type" => "figcaption"]
                ]
            ]
        ],
        plugins: [
            NodePluginExample::class
        ]
    );
    $v->check();


    $image = NodePluginExample::$image;
    $ref = NodePluginExample::$ref;
    $unrefimage = NodePluginExample::getUnrefered()['image'];
    expect($unrefimage)->toHaveCount(0);
    expect($image)->toHaveCount(1);
    expect($ref)->toHaveCount(1);
});


test("detect unrefered fig ", function () {

    UnreferedPlugin::reset();
    $v = Validator::make(
        nodes: [
            [
                "type" => "paragraph",
                "content" => [
                    [
                        "type" => "text",
                        "text" => str_repeat("Test ", 202)
                    ],
                ],

            ],
            [
                "type" => "imageFigure",
                "attrs" => ["id" => "fig:1"],
                "content" => [
                    ["type" => "image"],
                    ["type" => "figcaption"]
                ]
            ]
        ],
    );
    $v->check();
    $image = UnreferedPlugin::getUnrefered()['image'];
    expect($image)->toHaveCount(1);
    expect($image[0]["id"])->toBe("fig:1");
});
