@extends('order-base', ['top_text' => sprintf('Hi, %s.<br>The cleaner is at the property and the cleaning has started.', $user_name),
                       'middle_text' => sprintf('You can track the status of your cleaning using your Cleaning ID <b>(%s)</b> or click the button below to see more details.', $order_number),
                       'magic_link_text' => 'My ddd.com Cleaning'])