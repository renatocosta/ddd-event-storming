@extends('order-base-custom', ['top_text' => sprintf('%s has shared a cleaning report with you.', $user_name),
                       'middle_text' => $report_text,
                       'magic_link_text' => 'See the Cleaning Report'])