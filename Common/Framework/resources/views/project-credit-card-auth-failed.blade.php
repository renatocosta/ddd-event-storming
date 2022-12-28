@extends('order-base', ['top_text' => sprintf('Your credit card authorization for your cleaning for %s has failed.', $start_date),
                       'middle_text' => 'This might have happened because your credit card has an issue. You can change your credit card from the button below.',
                       'magic_link_text' => 'Change Credit Card'])