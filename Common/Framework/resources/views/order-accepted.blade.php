@extends('order-base', ['top_text' => 'Congrats! 🙌<br>A Cleaner accepted your request.', 
                        'middle_text' => sprintf('You can track the status of your cleaning using your Cleaning ID <b>(%s)</b> or click the button below to see more details.', $order_number),
                        'magic_link_text' => 'My ddd.com Cleaning'])