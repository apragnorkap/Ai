<?php
// Telegram Bot Token
$telegramBotToken = "7862035211:AAGfdXc2zjsVn4KZjRjRSuAPULg5fUJNASk";

// OpenAI API Key
$openaiApiKey = "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";

// Function to send a request to the OpenAI API
function askOpenAI($prompt) {
    global $openaiApiKey;

    $url = "https://api.openai.com/v1/chat/completions";
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer $openaiApiKey"
    ];
    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "You are a helpful assistant."],
            ["role" => "user", "content" => $prompt]
        ],
        "max_tokens" => 150
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Function to send a message to Telegram
function sendMessage($chatId, $text) {
    global $telegramBotToken;

    $url = "https://api.telegram.org/bot$telegramBotToken/sendMessage";
    $data = [
        "chat_id" => $chatId,
        "text" => $text
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Handle incoming updates from Telegram
$update = json_decode(file_get_contents("php://input"), true);

if (isset($update["message"])) {
    $chatId = $update["message"]["chat"]["id"];
    $text = $update["message"]["text"];

    // Get the response from OpenAI
    $openaiResponse = askOpenAI($text);

    if (isset($openaiResponse["choices"][0]["message"]["content"])) {
        $reply = $openaiResponse["choices"][0]["message"]["content"];
    } else {
        $reply = "Sorry, I couldn't generate a response.";
    }

    // Send the response back to Telegram
    sendMessage($chatId, $reply);
}
?>