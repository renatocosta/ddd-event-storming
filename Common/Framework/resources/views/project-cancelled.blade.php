@extends('order-base', ['top_text' => 'Oh no! The Cleaner has canceled the Cleaning.',
                       'middle_text' => sprintf('Your cleaning has been canceled by the Cleaner.<br><br>You can track the status of your cleaning using the code <b>%s</b> or click the button below to see more details.', $order_number),
                       'magic_link_text' => 'My ddd.com Cleaning'])
                       