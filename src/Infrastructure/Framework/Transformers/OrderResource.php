<?php

namespace Infrastructure\Framework\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param \Illuminate\Http\Request
   * @return array
   */
  public function toArray($request)
  {

    $data = $this->payload()->asArray()['event_data'];
    unset($data['customer']['payment']['payment_method_token']);
    unset($data['customer']['payment']['customer_token']);
    return [
      '_type'               => 'Order',
      'id'                  => $this->getIdentifier()->id,
      'user_id' => auth()->user()->id,
      'cleaners' => $data['cleaners'],
      'project' => $data['project'],
      'customer' => $data['customer'],
      'reviewed' => $this->reviewed()
    ];
  }
}
