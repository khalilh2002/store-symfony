<?php

namespace App\Service;


class TwilioService
{
    public function sendWhatsappOTP($recipientPhoneNumber, $WhatsappOTPCode)
    {
      
      //The idInstance and apiTokenInstance values are available in your account, double brackets must be removed
      $url = 'https://api.green-api.com/waInstance7103931857/sendMessage/078a024698694dcc97540eafb31d4aa2f47fac2bb038415b87';
      
      //chatId is the number to send the message to (@c.us for private chats, @g.us for group chats)
      $data = array(
          'chatId' => "$recipientPhoneNumber@c.us",
          'message' => "cod de verfication :  $WhatsappOTPCode"
      );
      
      $options = array(
          'http' => array(
              'header' => "Content-Type: application/json\r\n",
              'method' => 'POST',
              'content' => json_encode($data)
          )
      );
      
      $context = stream_context_create($options);
      
      $response = file_get_contents($url, false, $context);
      
      return $response;
              
    }
}
