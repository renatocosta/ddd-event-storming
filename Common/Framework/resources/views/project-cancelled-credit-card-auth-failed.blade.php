@extends('order-base', ['top_text' => 'Your Cleaning has been canceled.',
                       'middle_text' => sprintf('We were unable to complete the payment authorization on your credit card for your Cleaning on %s.<br><br>Your cleaning has been canceled.', $cancellation_date),
                       'magic_link' => '',  
                       'magic_link_text' => ''])